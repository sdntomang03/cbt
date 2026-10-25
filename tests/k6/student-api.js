import http from 'k6/http';
import { check, group, sleep } from 'k6';

const API_BASE_URL = (__ENV.API_BASE_URL || 'https://cbt.sdntomang03.sch.id/api/v1/student')
  .replace(/\/+$/, '');
const TOKEN = __ENV.K6_TOKEN;
const failureLogCounts = {};

export const options = {
stages: [
    { duration: '5s', target: 50 },  // Naikkan ke 50 user perlahan
    { duration: '25s', target: 100 }, // Tahan 100 user selama 25 detik
    { duration: '5s', target: 0 },  // Turunkan kembali ke 0
  ],
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<1000'],
  },
};

export function setup() {
  if (!TOKEN) {
    throw new Error('K6_TOKEN belum diatur. Berikan token Sanctum akun siswa pengujian.');
  }
}

function getStudentEndpoint(path, expectedData, name) {
  const response = http.get(`${API_BASE_URL}/${path}`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${TOKEN}`,
    },
    tags: { endpoint: name },
  });

  let body = null;
  try {
    body = response.json();
  } catch {
    // A failed/non-JSON response is reported by the checks below.
  }

  const isApiError = response.status !== 200 || body === null || body.success !== true;
  failureLogCounts[name] = failureLogCounts[name] || 0;
  if (isApiError && failureLogCounts[name] < 2) {
    console.error(
      `${name}: HTTP ${response.status}, success=${body === null ? 'non-JSON' : body.success}, `
      + `content-type=${response.headers['Content-Type'] || 'unknown'}`,
    );
    failureLogCounts[name] += 1;
  }

  check(response, {
    [`${name}: HTTP 200`]: (res) => res.status === 200,
  });
  check(body, {
    [`${name}: success response`]: (json) => json !== null && json.success === true,
    [`${name}: expected data shape`]: (json) => (
      json !== null
      && json.success === true
      && json.data !== null
      && json.data !== undefined
      && expectedData(json.data)
    ),
  });
}

export default function () {
  group('Student API read-only flow', () => {
    getStudentEndpoint('profile', (data) => typeof data.id !== 'undefined', 'profile');
    sleep(2);
    getStudentEndpoint('dashboard', (data) => data.stats !== undefined, 'dashboard');
    sleep(2);
    getStudentEndpoint('exams', Array.isArray, 'exams');
    sleep(2);
  });
}
