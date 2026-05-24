<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = @json($config);
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

        if (window.safeToast?.[icon]) {
            window.safeToast[icon](title, text);
            return;
        }

        alert(text ? `${title}\n${text}` : title);
    }

    async function confirmAction(options) {
        if (window.Swal) {
            return window.Swal.fire({
                icon: options.icon,
                title: options.title,
                text: options.text,
                showCancelButton: true,
                confirmButtonText: options.confirmButtonText,
                confirmButtonColor: options.confirmButtonColor || undefined,
            }).then((result) => result.isConfirmed);
        }

        return confirm(options.text || options.title);
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

    function reviewedBadge() {
        return 'inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800/40';
    }

    function canceledBadge() {
        return 'inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800/40';
    }

    function updateRecordCount() {
        const counter = document.querySelector(config.countSelector);
        if (!counter) return;

        const count = document.querySelectorAll(config.rowSelector).length;
        counter.textContent = `${count} ${count === 1 ? 'Record' : 'Records'}`;
    }

    document.querySelectorAll(config.deleteSelector).forEach((button) => {
        button.addEventListener('click', async function () {
            const id = button.value;
            const confirmed = await confirmAction({
                icon: 'warning',
                title: 'Delete record?',
                text: "You won't be able to revert this.",
                confirmButtonText: 'Yes, delete it',
                confirmButtonColor: '#dc2626',
            });

            if (!confirmed) return;

            button.disabled = true;
            button.classList.add('opacity-60', 'pointer-events-none');

            try {
                await postAction(routeWithId(config.deleteRoute, id));
                document.querySelector(`${config.rowSelector}.row-${id}`)?.remove();
                updateRecordCount();
                notify('success', 'Deleted', `${config.label} record deleted.`);
            } catch (error) {
                button.disabled = false;
                button.classList.remove('opacity-60', 'pointer-events-none');
                notify('error', 'Delete Failed', error.message);
            }
        });
    });

    document.querySelectorAll(config.approveSelector).forEach((button) => {
        button.addEventListener('click', async function () {
            const id = button.value;
            const confirmed = await confirmAction({
                icon: 'question',
                title: `Approve ${config.label.toLowerCase()}?`,
                text: 'This will mark the record as reviewed.',
                confirmButtonText: 'Yes, approve',
            });

            if (!confirmed) return;

            button.disabled = true;
            button.classList.add('opacity-60', 'pointer-events-none');

            try {
                await postAction(routeWithId(config.approveRoute, id));

                const status = document.getElementById(`status-${id}`);
                if (status) {
                    status.textContent = 'Reviewed';
                    status.className = reviewedBadge();
                }

                const row = document.querySelector(`${config.rowSelector}.row-${id}`);
                row?.querySelectorAll(config.afterApproveHideSelector || '[data-pds-hide-on-approve]')
                    .forEach((element) => element.remove());

                notify('success', 'Approved', `${config.label} record reviewed.`);
            } catch (error) {
                button.disabled = false;
                button.classList.remove('opacity-60', 'pointer-events-none');
                notify('error', 'Approve Failed', error.message);
            }
        });
    });

    document.querySelectorAll(config.cancelFormSelector || '[data-pds-cancel-form]').forEach((form) => {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitButton = form.querySelector('[type="submit"]');
            const formData = new FormData(form);
            const id = formData.get('id');
            if (!id) return;

            submitButton?.setAttribute('disabled', 'disabled');
            submitButton?.classList.add('opacity-60', 'pointer-events-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.status >= 400) {
                    throw new Error(data.message || 'The record could not be canceled.');
                }

                const status = document.getElementById(`status-${id}`);
                if (status) {
                    status.textContent = 'Canceled';
                    status.className = canceledBadge();
                }

                const row = document.querySelector(`${config.rowSelector}.row-${id}`);
                row?.querySelectorAll(config.afterApproveHideSelector || '[data-pds-hide-on-approve]')
                    .forEach((element) => element.remove());

                const remarks = formData.get('remarks');
                if (remarks && row && !row.querySelector('[data-pds-remarks]')) {
                    const actions = row.querySelector('[data-pds-actions]') || row.querySelector('.ml-auto');
                    const remarksEl = document.createElement('span');
                    remarksEl.dataset.pdsRemarks = 'true';
                    remarksEl.className = 'text-red-500';
                    remarksEl.textContent = `Remarks: ${remarks}`;
                    actions?.insertAdjacentElement('beforebegin', remarksEl);
                }

                document.querySelector(config.cancelModalSelector || '#cancel-modal-backdrop')
                    ?.classList.replace('flex', 'hidden');
                form.reset();
                notify('success', 'Canceled', `${config.label} record canceled.`);
            } catch (error) {
                notify('error', 'Cancel Failed', error.message);
            } finally {
                submitButton?.removeAttribute('disabled');
                submitButton?.classList.remove('opacity-60', 'pointer-events-none');
            }
        });
    });
});
</script>
