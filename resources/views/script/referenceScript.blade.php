<script>
document.addEventListener('DOMContentLoaded', () => {
    const empid = @json($empid);
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const timers = new Map();

    function sanitize(field) {
        field.value = String(field.value || '').replace(/;/g, '');
    }

    async function saveField(field) {
        if (!field.name) return;
        sanitize(field);

        try {
            const response = await fetch('{{ route("update.references") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    empid,
                    column: field.name,
                    index: field.dataset.array,
                    value: field.value,
                }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save reference.');
            }
        } catch (error) {
            console.error(error);
        }
    }

    function queueSave(field, delay = 500) {
        const key = `${field.name}-${field.dataset.array || ''}`;
        window.clearTimeout(timers.get(key));
        timers.set(key, window.setTimeout(() => saveField(field), delay));
    }

    document.querySelectorAll('.updated-data').forEach(field => {
        field.addEventListener('input', () => {
            sanitize(field);
            queueSave(field);
        });
        field.addEventListener('change', () => queueSave(field, 0));
        field.addEventListener('blur', () => queueSave(field, 0));
    });
});
</script>
