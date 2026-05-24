<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('signature-file');
    const preview = document.getElementById('signature-preview');
    const uploadUrl = @json(route('uploadSignature', $employee->id));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function notify(icon, title, text) {
        if (window.Swal) {
            window.Swal.fire({ icon, title, text });
            return;
        }

        if (window.Toast) {
            const method = icon === 'error' ? 'error' : 'success';
            window.Toast[method](title, text);
            return;
        }

        alert(`${title}\n${text}`);
    }

    preview?.addEventListener('click', function (event) {
        event.stopPropagation();
        input?.click();
    });

    input?.addEventListener('change', async function () {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        if (file.type !== 'image/png') {
            notify('error', 'Invalid File Type', 'Only PNG files are allowed.');
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('signature', file);

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                const message = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message || 'An unexpected error occurred.');

                notify('error', response.status === 422 ? 'Validation Error' : 'Upload Failed', message);
                return;
            }

            preview.src = data.image_url;
            notify('success', 'Signature Updated', 'Your signature was uploaded successfully.');
        } catch (error) {
            notify('error', 'Server Error', 'Unable to upload signature. Please try again.');
        } finally {
            input.value = '';
        }
    });
});
</script>
