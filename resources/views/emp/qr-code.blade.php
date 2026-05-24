<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>All Employee ID Cards</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: Arial, sans-serif;
        }
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 1px solid #d7dde8;
            background: rgba(255, 255, 255, .96);
        }
        .toolbar h1 {
            margin: 0;
            font-size: 16px;
            line-height: 1.2;
        }
        .toolbar p {
            margin: 3px 0 0;
            color: #64748b;
            font-size: 12px;
        }
        .print-button {
            border: 0;
            border-radius: 8px;
            background: #111827;
            color: #fff;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            padding: 9px 13px;
        }
        .print-button:disabled {
            cursor: wait;
            opacity: .55;
        }
        .sheet {
            display: grid;
            grid-template-columns: repeat(2, 2.125in);
            gap: .18in;
            justify-content: center;
            padding: 14px;
        }
        .id-card {
            position: relative;
            display: flex;
            width: 2.125in;
            height: 3.375in;
            break-inside: avoid;
            page-break-inside: avoid;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        }
        .card-head {
            min-height: .52in;
            padding: .12in .14in;
            background: #111827;
            color: #fff;
        }
        .system {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .office {
            overflow: hidden;
            margin-top: 2px;
            color: #cbd5e1;
            font-size: 7px;
            line-height: 1.1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .photo-wrap {
            display: flex;
            justify-content: center;
            margin-top: -.16in;
        }
        .photo,
        .initials {
            width: .9in;
            height: .9in;
            border: 3px solid #fff;
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, .2);
        }
        .photo {
            object-fit: cover;
        }
        .initials {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28px;
            font-weight: 900;
        }
        .name {
            margin: .11in .13in 0;
            min-height: .34in;
            font-size: 12px;
            font-weight: 900;
            line-height: 1.1;
            text-align: center;
            text-transform: uppercase;
        }
        .position {
            margin: .05in .13in 0;
            min-height: .24in;
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            line-height: 1.15;
            text-align: center;
            text-transform: uppercase;
        }
        .card-bottom {
            display: grid;
            grid-template-columns: minmax(0, 1fr) .68in;
            gap: .08in;
            align-items: end;
            margin-top: auto;
            padding: .12in;
        }
        .meta {
            min-width: 0;
            border-radius: 10px;
            background: #f1f5f9;
            padding: .08in;
        }
        .label {
            color: #64748b;
            font-size: 6px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .emp-id {
            overflow: hidden;
            margin-top: 2px;
            font-family: Consolas, monospace;
            font-size: 12px;
            font-weight: 900;
            line-height: 1.1;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .status {
            margin-top: .05in;
            font-size: 7px;
            font-weight: 800;
        }
        .qr-code {
            display: flex;
            width: .68in;
            height: .68in;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            padding: 3px;
        }
        .qr-code img,
        .qr-code canvas {
            width: 100% !important;
            height: 100% !important;
        }
        @media print {
            body {
                background: #fff;
            }
            .toolbar {
                display: none;
            }
            .sheet {
                grid-template-columns: repeat(3, 2.125in);
                gap: .15in;
                padding: 0;
            }
            .id-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <div>
            <h1 id="page-title">Generating All Employee ID Cards</h1>
            <p id="render-status">Preparing {{ number_format($employees->count()) }} cards...</p>
        </div>
        <button type="button" id="print-button" class="print-button" onclick="window.print()" disabled>Print / Save PDF</button>
    </div>

    <main class="sheet">
        @foreach($employees as $index => $employee)
            <section class="id-card">
                <div class="card-head">
                    <div class="system">Employee Identification</div>
                    <div class="office">{{ $employee->office_name ?: 'Office not set' }}</div>
                </div>

                <div class="photo-wrap">
                    @if($employee->profile_url)
                        <img src="{{ $employee->profile_url }}" alt="{{ $employee->display_name }}" class="photo" loading="lazy">
                    @else
                        <div class="initials" style="background-color: {{ $employee->initial_color }};">{{ $employee->initials }}</div>
                    @endif
                </div>

                <div class="name">{{ $employee->display_name }}</div>
                <div class="position">{{ $employee->position ?: 'No position set' }}</div>

                <div class="card-bottom">
                    <div class="meta">
                        <div class="label">Employee ID</div>
                        <div class="emp-id">{{ $employee->emp_ID }}</div>
                        <div class="status">{{ $employee->stat_1 == 1 ? 'Active' : 'Inactive' }}</div>
                    </div>
                    <div class="qr-code" data-index="{{ $index }}" data-token="{{ $employee->qr_token }}"></div>
                </div>
            </section>
        @endforeach
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const qrNodes = Array.from(document.querySelectorAll('.qr-code'));
        const title = document.getElementById('page-title');
        const status = document.getElementById('render-status');
        const printButton = document.getElementById('print-button');
        let cursor = 0;
        const batchSize = 32;

        function renderBatch() {
            const end = Math.min(cursor + batchSize, qrNodes.length);

            for (; cursor < end; cursor++) {
                const node = qrNodes[cursor];
                if (node.dataset.ready === '1') continue;

                new QRCode(node, {
                    text: node.dataset.token,
                    width: 78,
                    height: 78,
                    colorDark: '#000000',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
                node.dataset.ready = '1';
            }

            status.textContent = `Generated ${cursor.toLocaleString()} of ${qrNodes.length.toLocaleString()} cards`;

            if (cursor < qrNodes.length) {
                requestAnimationFrame(renderBatch);
                return;
            }

            title.textContent = 'All Employee ID Cards';
            status.textContent = `${qrNodes.length.toLocaleString()} cards ready for PDF display`;
            printButton.disabled = false;
            window.dispatchEvent(new Event('employee-id-cards-ready'));
        }

        window.addEventListener('load', renderBatch);
    </script>
</body>
</html>
