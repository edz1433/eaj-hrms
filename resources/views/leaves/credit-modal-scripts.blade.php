@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initializeLeaveCreditModalBridge();
        initializeSpecialLeaveSettings();
    });

    function initializeLeaveCreditModalBridge() {
        if (window.__leaveCreditModalsReady) {
            return;
        }

        window.__leaveCreditModalsReady = true;

        function openModal(modal) {
            if (!modal) {
                return;
            }

            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            modal.classList.add('show');
            document.body.classList.add('modal-open');

            if (!document.querySelector('.modal-backdrop.leave-credit-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show leave-credit-backdrop';
                document.body.appendChild(backdrop);
            }

            window.refreshIcons?.(modal);
        }

        function closeModal(modal) {
            if (!modal) {
                return;
            }

            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.modal-backdrop.leave-credit-backdrop').forEach((backdrop) => backdrop.remove());
        }

        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-toggle="modal"][data-target]');
            if (trigger) {
                const modal = document.querySelector(trigger.dataset.target.trim());
                if (modal) {
                    event.preventDefault();
                    openModal(modal);
                }
                return;
            }

            const dismiss = event.target.closest('[data-dismiss="modal"]');
            if (dismiss) {
                event.preventDefault();
                closeModal(dismiss.closest('.modal'));
                return;
            }

            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal(document.querySelector('.modal.show'));
            }
        });
    }

    function initializeSpecialLeaveSettings() {
        document.querySelectorAll('#modalSettingLeave .update-field').forEach((field) => {
            if (field.dataset.leaveSettingsReady === '1') {
                return;
            }

            field.dataset.leaveSettingsReady = '1';
            field.addEventListener('change', function () {
                saveSpecialLeaveBalance(field);
            });
        });
    }

    function saveSpecialLeaveBalance(field) {
        const employeeId = field.dataset.columnId;
        const column = field.dataset.columnName;
        const value = Number.parseFloat(field.value || 0);

        if (!employeeId || !column || Number.isNaN(value) || value < 0) {
            field.classList.add('border-destructive');
            return;
        }

        field.disabled = true;
        field.classList.remove('border-destructive');

        fetch('{{ route("employeeUpdate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: employeeId,
                column: column,
                value: value,
            }),
        })
        .then(async (response) => {
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to save balance.');
            }
            return data;
        })
        .then((data) => {
            const formatted = data.value || value.toFixed(3);
            const targetId = field.dataset.balanceTarget;
            field.value = formatted;

            if (targetId) {
                const balance = document.getElementById(targetId);
                if (balance) {
                    balance.textContent = formatted;
                }
            }

            window.safeToast?.success?.('Saved', 'Special leave balance updated.');
        })
        .catch((error) => {
            field.classList.add('border-destructive');
            window.safeToast?.error?.('Save failed', error.message || 'Unable to save balance.');
        })
        .finally(() => {
            field.disabled = false;
        });
    }
</script>
@endpush
