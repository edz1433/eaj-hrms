<style>
    .custom-gap .list-group-item {
        margin: 0px;
        padding: 2px;
    }
    .profile-image-container {
        width: 100px !important;
        height: 100px !important;
        border-radius: 50% !important;
        overflow: hidden !important;
        display: inline-block !important;
        position: relative !;
    }

    .profile-image-container img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 50% !important;
    }

    .mtop {
        margin-top: -15px;
    }
    .bg-form{
        background-color:  #e9ecef;
    }

    .form-control-sm {
        height: calc(1.5125rem + 2px);
        padding: .15rem .5rem;
        font-size: .750rem;
        line-height: 1.5;
        border-radius: .2rem;
        background-color: #ffffff !important;
    }
    .btn-sm{
        font-size: 10px !important;
        height: 25px !important;
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #ffffff;
        cursor: default;
    }
    .input-details {
        border: none;
        border-bottom: 1px solid #8f7f7f;
        padding: 0;
        outline: none;
        box-shadow: none;
        width: 220px;
    }
    .c-radio{
        width: 20px; 
        height: 20px; 
        padding-top: 2px;
        width: 20px; 
        height: 20px; 
        padding-top: 2px;
    }
    .c-label{
        border-radius: 3px; 
        padding: 2px; 
        width: 90px; 
        display: inline-block; 
        background-color: #FFFF;
    }

    .ft{
        font-size: 10px;
    }

    .bg-default{
        background-color: #3B8682 !important;
    }

    .glowing {
        border: 2px solid;
        animation: glowing 3.5s infinite;
    }

    @keyframes glowing {
        0% {
            border-color: #fff;
            box-shadow: 0 0 5px #fff;
        }
        50% {
            border-color: #00ff00;
            box-shadow: 0 0 10px #00ff00;
        }
        100% {
            border-color: #fff;
            box-shadow: 0 0 5px #fff;
        }
    }

    .download{
        margin-left: 59px !important;
        margin-top:  19px !important;
    }

    body.modal-open {
        overflow: hidden;
    }

    .modal {
        bottom: 0;
        display: none;
        left: 0;
        outline: 0;
        overflow-x: hidden;
        overflow-y: auto;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 1050;
    }

    .modal.show {
        display: block;
    }

    .modal-dialog {
        margin: 1.75rem auto;
        max-width: min(92vw, 32rem);
        pointer-events: none;
        position: relative;
        width: auto;
    }

    .modal-dialog-centered {
        align-items: center;
        display: flex;
        min-height: calc(100% - 3.5rem);
    }

    .modal-lg {
        max-width: min(92vw, 56rem);
    }

    .modal-md {
        max-width: min(92vw, 40rem);
    }

    .modal-content {
        pointer-events: auto;
        position: relative;
        width: 100%;
    }

    .modal-backdrop {
        background-color: rgb(15 23 42 / 0.5);
        bottom: 0;
        left: 0;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 1040;
    }

    .select2-container--default .select2-selection--single {
        min-height: 40px;
        border: 1px solid hsl(var(--border) / 0.6) !important;
        border-radius: 0.5rem !important;
        background: hsl(var(--background)) !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 0.75rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: hsl(var(--foreground)) !important;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 38px !important;
        padding-left: 0 !important;
        padding-right: 1.5rem !important;
        text-transform: uppercase;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        right: 0.5rem !important;
    }

    .select2-dropdown {
        border-color: hsl(var(--border)) !important;
        border-radius: 0.5rem !important;
        overflow: hidden;
        background: hsl(var(--popover, var(--card))) !important;
        color: hsl(var(--foreground)) !important;
    }

    .select2-search--dropdown {
        padding: 0.5rem !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid hsl(var(--border) / 0.7) !important;
        border-radius: 0.5rem !important;
        background: hsl(var(--background)) !important;
        color: hsl(var(--foreground)) !important;
        font-size: 0.875rem;
        outline: none;
        padding: 0.5rem 0.75rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: hsl(var(--primary)) !important;
        color: hsl(var(--primary-foreground)) !important;
    }

    .dataTables_wrapper {
        color: hsl(var(--foreground));
        font-size: 0.8125rem;
    }

    .leave-ledger-toolbar {
        align-items: center;
        border-bottom: 1px solid hsl(var(--border) / 0.6);
        display: flex;
        justify-content: flex-end;
        padding: 0.75rem;
    }

    .leave-ledger-toolbar .dataTables_filter {
        float: none;
        margin: 0;
        text-align: right;
        width: min(100%, 20rem);
    }

    .leave-ledger-toolbar .dataTables_filter label {
        display: block;
        margin: 0;
        width: 100%;
    }

    .leave-ledger-toolbar .dataTables_filter input {
        margin-left: 0 !important;
        width: 100%;
    }

    .leave-ledger-footer {
        align-items: center;
        border-top: 1px solid hsl(var(--border) / 0.6);
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: space-between;
        padding: 0.75rem;
    }

    .leave-ledger-footer .dataTables_info,
    .leave-ledger-footer .dataTables_paginate {
        float: none;
        padding: 0;
    }

    .leave-ledger-footer .dataTables_info {
        color: hsl(var(--muted-foreground));
        font-size: 0.75rem;
    }

    .leave-ledger-footer .dataTables_paginate {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        border: 1px solid hsl(var(--border) / 0.7);
        border-radius: 0.5rem;
        background: hsl(var(--background));
        color: hsl(var(--foreground));
        margin-left: 0.5rem;
        padding: 0.35rem 0.65rem;
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter input {
        min-height: 2.25rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: hsl(var(--primary) / 0.5);
        box-shadow: 0 0 0 3px hsl(var(--primary) / 0.12);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid transparent !important;
        border-radius: 0.5rem !important;
        color: hsl(var(--muted-foreground)) !important;
        margin-left: 0.15rem !important;
        padding: 0.35rem 0.65rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: transparent !important;
        border-color: transparent !important;
        color: hsl(var(--muted-foreground) / 0.45) !important;
        cursor: not-allowed !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        border-color: hsl(var(--primary) / 0.2) !important;
        background: hsl(var(--primary) / 0.1) !important;
        color: hsl(var(--primary)) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border-color: hsl(var(--border)) !important;
        background: hsl(var(--muted) / 0.6) !important;
        color: hsl(var(--foreground)) !important;
    }
</style>
