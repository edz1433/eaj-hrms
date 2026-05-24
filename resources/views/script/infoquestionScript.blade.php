<script>
document.addEventListener('DOMContentLoaded', () => {
    const empid = @json($empid);
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const timers = new Map();

    function sanitize(field) {
        if (!field.classList.contains('input-details')) return;
        field.value = String(field.value || '').replace(/,/g, '');
    }

    async function autosave(field, force = false) {
        if (!field.name || (field.disabled && !force)) return;

        sanitize(field);

        try {
            const response = await fetch('{{ route("update.info.question") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    empid: empid,
                    column: field.name,
                    index: field.dataset.array,
                    value: field.value,
                }),
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save.');
            }
        } catch (error) {
            console.error(error);
        }
    }

    function queueSave(field, delay = 500, force = false) {
        const key = `${field.name}-${field.dataset.array || ''}`;
        window.clearTimeout(timers.get(key));
        timers.set(key, window.setTimeout(() => autosave(field, force), delay));
    }

    function setQuestionDetails(index, yesSelected, saveCleared = false) {
        document.querySelectorAll(`[data-detail-for="${index}"]`).forEach(wrapper => {
            wrapper.classList.toggle('hidden', !yesSelected);
            wrapper.classList.toggle('flex', yesSelected);

            wrapper.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = !yesSelected;
                input.readOnly = !yesSelected;

                if (!yesSelected) {
                    input.value = '';
                    if (saveCleared) {
                        queueSave(input, 0, true);
                    }
                }
            });
        });
    }

    document.querySelectorAll('input[type="radio"][name^="question_"]:checked').forEach(radio => {
        setQuestionDetails(radio.dataset.array, radio.value === '1', false);
    });

    document.querySelectorAll('input[type="radio"][name^="question_"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const yesSelected = radio.value === '1';
            setQuestionDetails(radio.dataset.array, yesSelected, true);
            queueSave(radio, 0);
        });
    });

    document.querySelectorAll('.input-details').forEach(field => {
        field.addEventListener('input', () => {
            sanitize(field);
            queueSave(field);
        });

        field.addEventListener('change', () => queueSave(field, 0));
        field.addEventListener('blur', () => queueSave(field, 0));
    });
});
</script>
