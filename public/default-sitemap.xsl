<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
    xmlns:html="http://www.w3.org/TR/REC-html40"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml" lang="id">
            <head>
                <title>XML Sitemap - CBT Bersama SDN Tomang 03</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <style type="text/css">
                    body {
                        font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
                        color: #334155;
                        background-color: #f8fafc;
                        margin: 0;
                        padding: 40px 20px;
                    }
                    .container {
                        max-width: 1200px;
                        margin: 0 auto;
                        background: #ffffff;
                        padding: 40px;
                        border-radius: 24px;
                        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
                        border: 1px solid #e2e8f0;
                    }
                    .header {
                        border-bottom: 2px solid #f1f5f9;
                        padding-bottom: 24px;
                        margin-bottom: 28px;
                    }
                    h1 {
                        color: #0f172a;
                        margin: 0 0 8px 0;
                        font-size: 30px;
                        font-weight: 850;
                        letter-spacing: -0.75px;
                    }
                    p {
                        line-height: 1.6;
                        color: #64748b;
                        font-size: 15px;
                        margin: 0;
                    }
                    p a {
                        color: #4f46e5;
                        text-decoration: none;
                        font-weight: 600;
                    }
                    p a:hover {
                        text-decoration: underline;
                    }
                    .stats {
                        background-color: #f0eefc;
                        border: 1px solid #e0dbfa;
                        padding: 14px 20px;
                        border-radius: 12px;
                        color: #4338ca;
                        font-size: 14px;
                        font-weight: 700;
                        display: inline-flex;
                        align-items: center;
                        margin-bottom: 24px;
                    }
                    table {
                        width: 100%;
                        border-collapse: separate;
                        border-spacing: 0;
                        margin-top: 10px;
                        text-align: left;
                    }
                    th {
                        background-color: #4f46e5;
                        color: #ffffff;
                        padding: 16px 20px;
                        font-size: 12px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        border: none;
                    }
                    th:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
                    th:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
                    td {
                        padding: 16px 20px;
                        border-bottom: 1px solid #f1f5f9;
                        font-size: 14px;
                        word-break: break-all;
                        color: #334155;
                    }
                    tr:hover td {
                        background-color: #f8fafc;
                        cursor: pointer;
                    }
                    .url-link {
                        color: #4f46e5;
                        text-decoration: none;
                        font-weight: 600;
                    }
                    .url-link:hover {
                        color: #3730a3;
                        text-decoration: underline;
                    }
                    .badge {
                        display: inline-block;
                        padding: 4px 10px;
                        font-size: 11px;
                        font-weight: 700;
                        border-radius: 20px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .badge-freq {
                        background-color: #f0fdf4;
                        color: #166534;
                        border: 1px solid #bbf7d0;
                    }
                    .badge-priority {
                        background-color: #fffbeb;
                        color: #92400e;
                        border: 1px solid #fde68a;
                    }
                    .img-count {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        font-weight: 600;
                        color: #0284c7;
                        background: #f0f9ff;
                        padding: 2px 8px;
                        border-radius: 6px;
                        border: 1px solid #bae6fd;
                        font-size: 12px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>XML Peta Situs (Sitemap)</h1>
                        <p>Peta situs ini dibuat secara dinamis oleh aplikasi CBT Laravel untuk membantu indeksasi mesin pencari seperti Google, Bing, dan Yahoo. Informasi teknis lebih lanjut dapat ditemukan di <a href="https://sitemaps.org" target="_blank">sitemaps.org</a>.</p>
                    </div>
                    
                    <div class="stats">
                        Total URL Terdeteksi: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 55%;">URL (Lokasi Halaman)</th>
                                <th style="width: 10%;">Gambar</th>
                                <th style="width: 12%;">Frekuensi</th>
                                <th style="width: 10%;">Prioritas</th>
                                <th style="width: 13%;">Pembaruan Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url">
                                <tr>
                                    <td>
                                        <xsl:variable name="itemURL" select="sitemap:loc"/>
                                        <a href="{$itemURL}" class="url-link">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </a>
                                    </td>
                                    <td>
                                        <xsl:if test="count(image:image) &gt; 0">
                                            <span class="img-count">
                                                📷 <xsl:value-of select="count(image:image)"/>
                                            </span>
                                        </xsl:if>
                                        <xsl:if test="count(image:image) = 0">
                                            <span style="color: #cbd5e1;">-</span>
                                        </xsl:if>
                                    </td>
                                    <td>
                                        <span class="badge badge-freq">
                                            <xsl:value-of select="sitemap:changefreq"/>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority">
                                            <xsl:value-of select="sitemap:priority"/>
                                        </span>
                                    </td>
                                    <td style="color: #64748b; font-family: monospace; font-size: 13px;">
                                        <xsl:value-of select="sitemap:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
