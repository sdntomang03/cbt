@props(['label' => null, 'height' => '200px', 'mini' => false])

<div class="w-full" x-data="richEditor({
         height: '{{ $height }}',
         mini: {{ $mini ? 'true' : 'false' }}
     })" x-modelable="content" {{ $attributes }}>

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
            Alpine.data('richEditor', (config) => ({
                editor: null,
                content: '',

                init() {
                    this.$nextTick(() => {
                        const toolbar = config.mini
                            ? [['bold', 'italic', 'underline', 'math', 'image']]
                            : [
                                ['undo', 'redo'],
                                ['formatBlock', 'font', 'fontSize'],
                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'removeFormat'],
                                ['fontColor', 'hiliteColor', 'list', 'table', 'math', 'image', 'video', 'codeView']
                              ];

                        const uploadConfig = window.globalUploadConfig || {};

                        this.editor = SUNEDITOR.create(this.$refs.sunEditorInput, {
                            height: 'auto',
                            minHeight: config.height,
                            maxHeight: config.mini ? null : '500px',

                            // PERUBAHAN DI SINI:
                            // Paksa semua mode menjadi 'classic' agar toolbar menempel di atas
                            mode: 'classic',

                            // Tetap sembunyikan bar resize di bawah jika mode mini
                            resizingBar: !config.mini,

                            buttonList: toolbar,
                            katex: window.katex,
                            ...uploadConfig
                        });

                        // 1. Initial Load
                        if (this.content) {
                            this.editor.setContents(this.content);
                        }

                        // 2. Editor -> Alpine
                        this.editor.onChange = (contents) => {
                            this.content = contents;
                        };

                        // 3. Alpine -> Editor (Watcher)
                        this.$watch('content', (value) => {
                            if (this.editor && value !== this.editor.getContents()) {
                                this.editor.setContents(value);
                            }
                        });
                    });
                }
            }));
        });
</script>
@endpush
@endonce
