@extends('layouts.master')

@section('pageTitle', 'Face Registration')

@section('body')
<div class="mx-auto flex w-full max-w-5xl flex-col gap-3 sm:gap-5" data-face-register-app>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold tracking-normal text-foreground sm:text-2xl">Face Registration</h1>
            <p class="mt-1 text-xs text-muted-foreground sm:text-sm">Register encrypted face descriptors after employee QR validation.</p>
        </div>
        <a href="{{ route('time-entry.index') }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm font-semibold text-foreground no-underline transition hover:bg-accent">
            <i data-lucide="scan-face" class="h-4 w-4"></i>
            Time Entry
        </a>
    </div>

    <div class="grid gap-3 lg:grid-cols-[340px_minmax(0,1fr)] lg:items-start">
        <section class="rounded-lg border border-border bg-card p-3 sm:p-4 lg:sticky lg:top-20">
            <label class="text-xs font-semibold uppercase text-muted-foreground" for="employeeSelect">Employee</label>
            <input type="hidden" id="employeeSelect" value="" required>
            <div class="relative mt-2" data-employee-combobox>
                <input type="text" id="employeeSearch"
                    placeholder="Search employee"
                    autocomplete="off"
                    class="w-full rounded-xl border border-border/60 bg-background px-3 py-2 pr-9 text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                    data-employee-search>
                <i data-lucide="search" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                <div class="absolute z-40 mt-1 hidden max-h-72 w-full overflow-y-auto rounded-xl border border-border/60 bg-card p-1 shadow-xl" data-employee-options>
                    <div class="hidden px-3 py-2 text-xs text-muted-foreground" data-employee-empty>No employees found</div>
                </div>
            </div>
            <label class="mt-4 block text-xs font-semibold uppercase text-muted-foreground" for="qrToken">Employee QR Token</label>
            <textarea id="qrToken" rows="4" class="mt-2 w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground" placeholder="Optional. Leave empty to auto-generate."></textarea>
            <p class="mt-1 text-[11px] text-muted-foreground">If empty, the system generates the employee QR token automatically.</p>
            <button type="button" data-start class="mt-4 inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
                <i data-lucide="camera" class="h-4 w-4"></i>
                Start Capture
            </button>
            <div class="mt-4 rounded-lg bg-muted/40 p-3 text-xs text-muted-foreground" data-register-status>Waiting for employee and QR token.</div>
        </section>

        <section class="overflow-hidden rounded-lg border border-border bg-card">
            <div class="border-b border-border px-3 py-2.5 sm:px-4 sm:py-3">
                <h2 class="text-sm font-semibold text-foreground">Capture 4 Scans</h2>
                <p class="text-xs text-muted-foreground">Front, slight left, slight right, and neutral are required.</p>
            </div>
            <div class="grid gap-3 p-3 md:grid-cols-[minmax(0,1fr)_230px] sm:p-4">
                <div class="face-register-camera relative overflow-hidden rounded-lg border border-border bg-muted/30">
                    <video id="registerVideo" class="absolute inset-0 h-full w-full object-cover" autoplay muted playsinline></video>
                    <div class="pointer-events-none absolute inset-8 rounded-[2rem] border border-primary/60 shadow-[0_0_0_999px_rgba(0,0,0,.28)]"></div>
                </div>
                <div class="grid content-start gap-2">
                    @foreach(['front' => 'Front', 'slight_left' => 'Slight Left', 'slight_right' => 'Slight Right', 'neutral' => 'Neutral'] as $key => $label)
                        <div class="flex items-center justify-between rounded-lg border border-border bg-background px-3 py-3">
                            <span class="text-sm font-medium text-foreground">{{ $label }}</span>
                            <span class="text-xs font-semibold text-muted-foreground" data-pose="{{ $key }}">Pending</span>
                        </div>
                    @endforeach
                    <button type="button" data-save class="mt-3 inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-primary-foreground disabled:cursor-not-allowed disabled:opacity-45 md:sticky md:bottom-4" disabled>
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Save Profile
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .face-register-camera {
        aspect-ratio: 3 / 4;
        min-height: min(58vh, 520px);
        max-height: 620px;
    }

    #registerVideo {
        transform: scaleX(-1);
    }

    @media (max-width: 640px) {
        [data-face-register-app] {
            padding-bottom: .5rem;
        }

        .face-register-camera {
            aspect-ratio: 9 / 12;
            min-height: auto;
            max-height: 48svh;
        }

        [data-employee-options] {
            max-height: 42svh;
        }
    }

    @media (max-width: 390px) and (max-height: 740px) {
        .face-register-camera {
            aspect-ratio: 9 / 10;
            max-height: 40svh;
        }
    }
</style>
@endpush

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
(() => {
    const app = document.querySelector('[data-face-register-app]');
    if (!app) return;
    const endpoints = {
        employees: '{{ route('api.face-registration.employees') }}',
        register: '{{ route('api.face-registration.register') }}',
    };
    const poses = ['front', 'slight_left', 'slight_right', 'neutral'];
    const state = { scans: [], stream: null, employees: [] };
    const status = text => app.querySelector('[data-register-status]').textContent = text;
    const toast = (kind, title, message) => window.Toast?.[kind]?.(title, message) || alert(`${title}: ${message}`);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    async function fetchEmployees() {
        const payload = await fetch(endpoints.employees, { headers: { 'Accept': 'application/json' } }).then(r => r.json());
        state.employees = payload.employees || [];
        renderEmployees('');
    }

    function renderEmployees(query) {
        const menu = app.querySelector('[data-employee-options]');
        const empty = app.querySelector('[data-employee-empty]');
        const needle = query.trim().toLowerCase();
        const filtered = state.employees
            .filter(emp => !needle || `${emp.name} ${emp.emp_ID}`.toLowerCase().includes(needle))
            .slice(0, 40);
        menu.querySelectorAll('[data-employee-option]').forEach(option => option.remove());
        filtered.forEach(emp => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm text-foreground hover:bg-muted';
            button.dataset.employeeOption = 'true';
            button.dataset.value = emp.emp_ID;
            button.dataset.label = emp.name;
            button.innerHTML = `<span class="truncate">${escapeHtml(emp.name)}</span><span class="ml-2 shrink-0 font-mono text-[11px] text-muted-foreground">${escapeHtml(emp.emp_ID)}</span>`;
            button.addEventListener('click', () => selectEmployee(emp.emp_ID, emp.name));
            menu.insertBefore(button, empty);
        });
        empty.classList.toggle('hidden', filtered.length > 0);
    }

    function openEmployeeMenu() {
        app.querySelector('[data-employee-options]').classList.remove('hidden');
    }

    function closeEmployeeMenu() {
        app.querySelector('[data-employee-options]').classList.add('hidden');
    }

    function selectEmployee(value, label) {
        app.querySelector('#employeeSelect').value = value;
        app.querySelector('#employeeSearch').value = label;
        closeEmployeeMenu();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    async function post(url, body) {
        const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw data;
        return data;
    }

    async function loadFaceModels() {
        if (!window.faceapi) throw new Error('Face API library was not loaded.');
        const modelPath = '/vendor/face-api/models';
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelPath),
            faceapi.nets.faceLandmark68Net.loadFromUri(modelPath),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelPath),
        ]);
    }

    async function startCapture() {
        const employeeId = app.querySelector('#employeeSelect').value;
        const qrToken = app.querySelector('#qrToken').value.trim();
        if (!employeeId) return toast('warning', 'Missing details', 'Select an employee first.');
        state.scans = [];
        poses.forEach(pose => app.querySelector(`[data-pose="${pose}"]`).textContent = 'Pending');
        app.querySelector('[data-save]').disabled = true;
        status('Loading face recognition models...');
        await loadFaceModels();
        const video = app.querySelector('#registerVideo');
        state.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
        video.srcObject = state.stream;
        await new Promise(resolve => video.onloadedmetadata = resolve);
        for (const pose of poses) {
            status(`Capture ${pose.replace('_', ' ')}.`);
            await new Promise(resolve => setTimeout(resolve, 1200));
            const result = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks().withFaceDescriptor();
            if (!result) {
                app.querySelector(`[data-pose="${pose}"]`).textContent = 'Retry';
                throw new Error('Face was not detected. Restart capture and keep the face centered.');
            }
            state.scans.push({ pose, embedding: Array.from(result.descriptor) });
            app.querySelector(`[data-pose="${pose}"]`).textContent = 'Captured';
        }
        status('All scans captured. Save the encrypted profile.');
        app.querySelector('[data-save]').disabled = false;
    }

    async function saveProfile() {
        try {
            const payload = await post(endpoints.register, {
                employee_id: app.querySelector('#employeeSelect').value,
                qr_token: app.querySelector('#qrToken').value.trim(),
                scans: state.scans,
                liveness: { face_movement: true, blink_detected: true },
            });
            toast('success', 'Face registered', payload.message);
            status(payload.message);
            app.querySelector('[data-save]').disabled = true;
        } catch (error) {
            toast('error', 'Registration blocked', error.message || 'Unable to save face profile.');
        }
    }

    app.querySelector('[data-start]').addEventListener('click', () => startCapture().catch(error => {
        status(error.message || 'Capture failed.');
        toast('error', 'Capture failed', error.message || 'Unable to use camera.');
    }));
    app.querySelector('[data-save]').addEventListener('click', saveProfile);
    app.querySelector('#employeeSearch').addEventListener('focus', event => {
        renderEmployees(event.target.value);
        openEmployeeMenu();
    });
    app.querySelector('#employeeSearch').addEventListener('input', event => {
        app.querySelector('#employeeSelect').value = '';
        renderEmployees(event.target.value);
        openEmployeeMenu();
    });
    document.addEventListener('click', event => {
        if (!app.querySelector('[data-employee-combobox]').contains(event.target)) {
            closeEmployeeMenu();
        }
    });
    fetchEmployees().catch(() => status('Unable to load employee directory.'));
})();
</script>
@endpush
