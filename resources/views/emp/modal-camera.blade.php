{{-- Camera capture modal (Tailwind) --}}
<div id="camera-modal-backdrop"
     class="fixed inset-0 z-[60] hidden items-center justify-center p-4"
     aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCameraModal()"></div>

    <div class="relative w-full max-w-sm rounded-2xl border border-border/60 bg-card shadow-2xl">
        <div class="flex items-center justify-between border-b border-border/60 px-5 py-4">
            <h3 class="text-sm font-semibold text-foreground">
                <i class="fas fa-camera mr-2 text-primary text-sm"></i>Capture Profile Photo
            </h3>
            <button type="button" onclick="closeCameraModal()"
                class="rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <div class="p-5 space-y-3">
            <div id="camera" class="relative overflow-hidden rounded-xl bg-muted aspect-video">
                <div id="capture-image" class="hidden absolute inset-0"></div>
                <video id="webcam-preview" class="w-full h-full object-cover" autoplay playsinline></video>
            </div>

            <div class="flex gap-2">
                <button type="button" id="capture-button"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-primary px-3 py-2 text-xs font-semibold text-white transition hover:bg-primary/90">
                    <i class="fas fa-camera"></i> Capture
                </button>
                <button type="button" id="capture-again-button"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-border/60 bg-background px-3 py-2 text-xs font-medium text-foreground transition hover:bg-muted">
                    <i class="fas fa-redo"></i> Recapture
                </button>
                <button type="button" onclick="closeCameraModal()"
                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-800/40 dark:bg-red-950/30">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCameraModal() {
    document.getElementById('camera-modal-backdrop').classList.replace('hidden', 'flex');
}
function closeCameraModal() {
    document.getElementById('camera-modal-backdrop').classList.replace('flex', 'hidden');
}
</script>
