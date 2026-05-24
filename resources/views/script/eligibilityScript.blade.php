<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function routeWithId(template, id) {
        return template.replace('__ID__', encodeURIComponent(id));
    }

    function notify(icon, title, text = '') {
        if (window.Swal) {
            window.Swal.fire({
                icon,
                title,
                text,
                timer: icon === 'success' ? 1200 : undefined,
                showConfirmButton: icon !== 'success',
            });
            return;
        }

        if (window.Toast) {
            const method = icon === 'error' ? 'error' : 'success';
            window.Toast[method](title, text);
            return;
        }

        alert(text ? `${title}\n${text}` : title);
    }

    async function postAction(url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok || data.status >= 400) {
            throw new Error(data.message || 'The request could not be completed.');
        }

        return data;
    }

    document.querySelectorAll('.eligible_delete').forEach((button) => {
        button.addEventListener('click', async function () {
            const id = button.value;
            const url = routeWithId(@json(route('eliDelete', ['id' => '__ID__'])), id);

            const confirmed = window.Swal
                ? await window.Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                }).then((result) => result.isConfirmed)
                : confirm("Delete this eligibility record?");

            if (!confirmed) {
                return;
            }

            try {
                await postAction(url);
                document.querySelector(`.eligibility-row.row-${id}`)?.remove();
                notify('success', 'Deleted', 'Eligibility record deleted.');
            } catch (error) {
                notify('error', 'Delete Failed', error.message);
            }
        });
    });

    document.querySelectorAll('.eligible_approve').forEach((button) => {
        button.addEventListener('click', async function () {
            const id = button.value;
            const url = routeWithId(@json(route('eliApprove', ['id' => '__ID__'])), id);

            const confirmed = window.Swal
                ? await window.Swal.fire({
                    title: 'Approve eligibility?',
                    text: 'This will mark the record as reviewed.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, approve',
                }).then((result) => result.isConfirmed)
                : confirm('Approve this eligibility record?');

            if (!confirmed) {
                return;
            }

            try {
                await postAction(url);
                const status = document.getElementById(`status-${id}`);
                if (status) {
                    status.textContent = 'Reviewed';
                    status.className = 'inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800/40';
                }
                notify('success', 'Approved', 'Eligibility record reviewed.');
            } catch (error) {
                notify('error', 'Approve Failed', error.message);
            }
        });
    });
});
</script>
