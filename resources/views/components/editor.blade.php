@props(['label' => null, 'height' => '200px', 'mini' => false])

<div class="w-full" x-data="richEditor({ height: '{{ $height }}', mini: {{ $mini ? 'true' : 'false' }} })"
    x-modelable="content" {{ $attributes }}>

    @if($label)
    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">
        {!! $label !!}
    </label>
    @endif

    <div wire:ignore
        class="{{ $mini ? 'mini-editor rounded-2xl' : 'bg-white rounded-2xl shadow-sm border border-slate-200' }} overflow-hidden">
        <textarea x-ref="sunEditorInput" class="w-full hidden"></textarea>
    </div>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Cegah re-deklarasi komponen
        if (Alpine.data('richEditor')) return;

        Alpine.data('richEditor', (config) => ({
            editor: null,
            content: '',

            init() {
                this.$nextTick(() => {
                    // Pastikan SunEditor sudah ter-load dari CDN
                    if (typeof window.SUNEDITOR === 'undefined') {
                        console.error('SunEditor belum dimuat!');
                        return;
                    }

                    const toolbar = config.mini
                        ? [['bold', 'italic', 'underline', 'math', 'image']]
                        : [
                            ['undo', 'redo'],
                            ['formatBlock', 'font', 'fontSize'],
                            ['bold', 'italic', 'underline', 'strike', 'removeFormat'],
                            ['fontColor', 'hiliteColor', 'list', 'table', 'math', 'image', 'video', 'codeView']
                        ];

                    this.editor = window.SUNEDITOR.create(this.$refs.sunEditorInput, {
                        height: 'auto',
                        minHeight: config.height,
                        maxHeight: config.mini ? null : '400px',
                        katex: window.katex,

                        // --- FIX ERROR SIMPAN KATEX ---
                        katexOnlyText: true,
                        attributesWhitelist: { 'span': 'contenteditable|data-exp|data-font-size' },
                        // ------------------------------

                        buttonList: toolbar,
                        mode: 'classic',
                        defaultStyle: "font-family: 'Nunito', sans-serif; font-size: 16px;"
                    });

                    // 1. Initial Load (Jika ada value sebelumnya)
                    if (this.content) {
                        this.editor.setContents(this.content);
                    }

                    // 2. Editor -> Alpine
                    this.editor.onChange = (contents) => {
                        this.content = contents;
                        this.$el.dispatchEvent(new CustomEvent('input', { detail: contents, bubbles: true }));
                    };

                    // 3. Alpine -> Editor (Penting untuk ganti soal / Edit mode)
                    this.$watch('content', (value) => {
                        if (this.editor && value !== this.editor.getContents()) {
                            this.editor.setContents(value || '');
                        }
                    });
                });
            },

            destroy() {
                if (this.editor) this.editor.destroy();
            }
        }));
    });
</script>
@endpush
@endonce