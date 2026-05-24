@extends('layouts.time-entry-public')

@section('pageTitle', 'Time Entry')

@section('body')
<div class="mx-auto flex w-full max-w-5xl flex-col gap-3 md:gap-4" data-time-entry-app>
    <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg font-semibold tracking-normal text-foreground sm:text-2xl">Time Entry</h1>
            <p class="mt-0.5 text-xs text-muted-foreground sm:text-sm">Scan QR, choose action, verify live face.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('time-entry.register') }}" data-admin-link class="hidden h-10 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm font-semibold text-foreground no-underline transition hover:bg-accent">
                <i data-lucide="shield-check" class="h-4 w-4"></i>
                Register Face
            </a>
            <a href="{{ route('time-entry.logs') }}" data-admin-link class="hidden h-10 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm font-semibold text-foreground no-underline transition hover:bg-accent">
                <i data-lucide="list-checks" class="h-4 w-4"></i>
                Logs
            </a>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section class="overflow-hidden rounded-lg border border-border bg-card">
            <div class="flex items-center justify-between border-b border-border px-3 py-2.5 sm:px-4 sm:py-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary sm:h-9 sm:w-9"><i data-lucide="scan-qr-code" class="h-4 w-4"></i></span>
                    <div>
                        <h2 class="text-sm font-semibold text-foreground" data-stage-title>QR Scanner</h2>
                        <p class="text-xs text-muted-foreground" data-stage-subtitle>Scan your personal QR code.</p>
                    </div>
                </div>
                <span class="rounded-full border border-border px-2 py-1 text-xs font-medium text-muted-foreground" data-clock>--:--:--</span>
            </div>

            <div class="grid gap-3 p-3 md:grid-cols-[minmax(0,1fr)_300px] lg:p-4">
                <div class="time-entry-camera relative overflow-hidden rounded-lg border border-border bg-muted/30">
                    <div id="qr-reader" class="absolute inset-0 h-full w-full"></div>
                    <video id="face-video" class="absolute inset-0 hidden h-full w-full object-cover" autoplay muted playsinline></video>
                    <div class="pointer-events-none absolute left-3 right-3 top-3 z-10 hidden rounded-lg border border-border/60 bg-background/90 px-3 py-2 backdrop-blur" data-camera-employee>
                        <p class="truncate text-sm font-semibold text-foreground" data-camera-employee-name>No employee scanned</p>
                        <p class="truncate text-xs text-muted-foreground" data-camera-employee-meta>Scan QR to continue</p>
                    </div>
                    <div class="pointer-events-none absolute inset-0">
                        <div class="absolute inset-7 rounded-[1.75rem] border border-primary/70 shadow-[0_0_0_999px_rgba(0,0,0,.30)]"></div>
                        <div class="absolute left-1/2 top-1/2 h-28 w-28 -translate-x-1/2 -translate-y-1/2 rounded-full border border-primary/40"></div>
                        <div class="absolute bottom-4 left-4 right-4 rounded-lg bg-background/90 px-3 py-2 text-xs text-foreground backdrop-blur" data-scanner-status>Camera warming up...</div>
                    </div>
                </div>

                <div class="time-entry-panel hidden gap-2 sm:gap-3" data-post-qr-panel>
                    <div class="rounded-lg border border-border bg-background p-3">
                        <p class="text-[11px] font-semibold uppercase text-muted-foreground">Verification</p>
                        <div class="mt-2 grid grid-cols-3 gap-1.5">
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5 text-center"><span class="block text-[11px] text-muted-foreground">QR</span><span class="block text-xs font-semibold" data-qr-state>Waiting</span></div>
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5 text-center"><span class="block text-[11px] text-muted-foreground">Face</span><span class="block text-xs font-semibold" data-face-state>Locked</span></div>
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5 text-center"><span class="block text-[11px] text-muted-foreground">Geo</span><span class="block text-xs font-semibold" data-geo-state>Pending</span></div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border bg-background p-3">
                        <p class="text-[11px] font-semibold uppercase text-muted-foreground">Employee</p>
                        <div class="mt-2 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary"><i data-lucide="user-round-check" class="h-4 w-4"></i></div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-foreground" data-employee-name>No employee scanned</p>
                                <p class="truncate text-xs text-muted-foreground" data-employee-id>--</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border bg-background p-3">
                        <p class="text-[11px] font-semibold uppercase text-muted-foreground">Available Actions</p>
                        <div class="mt-2 grid grid-cols-3 gap-1.5">
                            <button type="button" data-action="time_in" class="time-entry-action h-10 rounded-lg bg-primary px-2 text-xs font-semibold text-primary-foreground disabled:cursor-not-allowed disabled:opacity-45 sm:text-sm">Time In</button>
                            <button type="button" data-action="time_out" class="time-entry-action h-10 rounded-lg bg-primary px-2 text-xs font-semibold text-primary-foreground disabled:cursor-not-allowed disabled:opacity-45 sm:text-sm">Time Out</button>
                            <button type="button" data-action="overtime" class="time-entry-action h-10 rounded-lg border border-border bg-card px-2 text-xs font-semibold text-foreground disabled:cursor-not-allowed disabled:opacity-45 sm:text-sm">Overtime</button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-border bg-background p-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[11px] font-semibold uppercase text-muted-foreground">Session Status</p>
                            <button type="button" data-reset class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-border bg-card text-foreground transition hover:bg-accent" aria-label="Restart scan">
                                <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <p class="mt-2 truncate text-xs text-muted-foreground" data-location-text>Location will be captured after face confirmation.</p>
                        <div class="mt-2 grid grid-cols-3 gap-1.5 text-center">
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5"><span class="block text-[11px] text-muted-foreground">In</span><span class="block truncate text-xs font-semibold" data-dtr-in>--</span></div>
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5"><span class="block text-[11px] text-muted-foreground">Out</span><span class="block truncate text-xs font-semibold" data-dtr-out>--</span></div>
                            <div class="rounded-lg bg-muted/40 px-2 py-1.5"><span class="block text-[11px] text-muted-foreground">OT</span><span class="block truncate text-xs font-semibold" data-dtr-ot>--</span></div>
                        </div>
                        <pre class="sr-only" data-dtr-json>{}</pre>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .time-entry-camera {
        aspect-ratio: 3 / 4;
        min-height: min(62vh, 560px);
        max-height: 620px;
    }

    .time-entry-panel {
        align-content: start;
    }

    #qr-reader,
    #qr-reader video,
    #qr-reader__scan_region,
    #qr-reader__scan_region video {
        height: 100% !important;
        width: 100% !important;
        object-fit: cover !important;
    }

    #qr-reader__dashboard_section,
    #qr-reader__header_message {
        display: none !important;
    }

    [data-scanner-status]:empty {
        display: none;
    }

    #face-video {
        transform: scaleX(-1);
    }

    @media (max-width: 640px) {
        body {
            overflow-x: hidden;
        }

        .time-entry-camera {
            aspect-ratio: 9 / 14;
            min-height: auto;
            max-height: min(58vh, 500px);
        }

        .time-entry-panel {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 420px) and (max-height: 820px) {
        .time-entry-camera {
            aspect-ratio: 9 / 12;
            max-height: 52vh;
        }
    }

    @media (max-width: 390px) and (max-height: 720px) {
        .time-entry-camera {
            aspect-ratio: 9 / 10;
            max-height: 46vh;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
(() => {
    const app = document.querySelector('[data-time-entry-app]');
    if (!app) return;

    const api = {
        qr: '{{ route('api.time-entry.validate-qr') }}',
        face: '{{ route('api.time-entry.verify-face') }}',
        log: '{{ route('api.time-entry.log-attendance') }}',
        status: '{{ route('api.time-entry.status') }}',
    };

    const state = { qrToken: null, employee: null, selectedAction: null, embedding: null, liveness: null, geo: null, status: null, faceVerified: false, canRegisterFace: false, qrScanner: null, faceStream: null };
    const setText = (selector, text) => { const el = app.querySelector(selector); if (el) el.textContent = text; };
    const toast = (kind, title, message) => window.Toast?.[kind]?.(title, message) || alert(`${title}: ${message}`);
    const post = (url, body) => fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }, body: JSON.stringify(body) }).then(async r => {
        const data = await r.json().catch(() => ({}));
        if (data.csrf_token) document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrf_token);
        if (!r.ok) throw data;
        return data;
    });

    function clock() {
        setText('[data-clock]', new Date().toLocaleTimeString());
        requestAnimationFrame(() => setTimeout(clock, 500));
    }

    function resetActions() {
        app.querySelectorAll('.time-entry-action').forEach(btn => btn.disabled = true);
    }

    function updateActions(status) {
        resetActions();
        state.status = status;
        setText('[data-dtr-json]', JSON.stringify(status || {}, null, 2));
        setText('[data-dtr-in]', status?.time_in?.[0] || '--');
        setText('[data-dtr-out]', status?.time_out?.[0] || '--');
        setText('[data-dtr-ot]', status?.overtime?.[0] || '--');
        if (!status?.actions || !state.employee || !state.faceVerified) return;
        Object.entries(status.actions).forEach(([action, enabled]) => {
            const btn = app.querySelector(`[data-action="${action}"]`);
            if (btn) btn.disabled = !enabled;
        });
    }

    async function startQr() {
        if (!canUseCamera()) return;
        resetActions();
        stopFace();
        app.querySelector('#face-video').classList.add('hidden');
        app.querySelector('#qr-reader').classList.remove('hidden');
        app.querySelector('[data-camera-employee]').classList.add('hidden');
        app.querySelector('[data-post-qr-panel]').classList.add('hidden');
        app.querySelector('[data-post-qr-panel]').classList.remove('grid');
        setText('[data-stage-title]', 'QR Scanner');
        setText('[data-stage-subtitle]', 'Scan your personal QR code.');
        setText('[data-scanner-status]', '');
        state.qrScanner = new Html5Qrcode('qr-reader');
        await state.qrScanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, onQrScan);
    }

    async function onQrScan(decodedText) {
        if (!decodedText || state.qrToken) return;
        state.qrToken = decodedText;
        setText('[data-qr-state]', 'Checking');
        await state.qrScanner?.stop().catch(() => {});
        try {
            const payload = await post(api.qr, { qr_token: decodedText });
            state.employee = payload.employee;
            setText('[data-qr-state]', 'Passed');
            setText('[data-employee-name]', payload.employee.name);
            setText('[data-employee-id]', payload.employee.emp_ID);
            setText('[data-camera-employee-name]', payload.employee.name);
            setText('[data-camera-employee-meta]', `${payload.employee.emp_ID} - verify live face`);
            app.querySelector('[data-camera-employee]').classList.remove('hidden');
            setText('[data-stage-title]', 'Face Verification');
            setText('[data-stage-subtitle]', 'Verify live face to unlock actions.');
            setText('[data-scanner-status]', 'QR verified. Starting live face confirmation.');
        } catch (error) {
            setText('[data-qr-state]', 'Failed');
            toast('error', 'QR rejected', error.message || 'Unable to validate QR token.');
            state.qrToken = null;
            await startQr();
            return;
        }

        try {
            await startFace();
        } catch (error) {
            setText('[data-face-state]', 'Failed');
            toast('error', 'Face confirmation failed', error.message || 'Unable to verify live face.');
            setText('[data-stage-title]', 'Face Verification');
            setText('[data-stage-subtitle]', 'Restart scan to try again.');
            setText('[data-scanner-status]', 'Live face confirmation failed. Restart scan to try again.');
        }
    }

    async function startFace() {
        if (!canUseCamera()) return;
        state.embedding = null;
        state.liveness = null;
        state.geo = null;
        resetActions();
        setText('[data-stage-title]', 'Face Verification');
        setText('[data-stage-subtitle]', 'Complete the live face challenge.');
        setText('[data-face-state]', 'Loading');
        setText('[data-scanner-status]', 'Loading face recognition models...');
        await loadFaceModels();
        app.querySelector('#qr-reader').classList.add('hidden');
        const video = app.querySelector('#face-video');
        video.classList.remove('hidden');
        state.faceStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
        video.srcObject = state.faceStream;
        await new Promise(resolve => video.onloadedmetadata = resolve);
        await captureFace(video);
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

    async function captureFace(video) {
        const samples = [];
        const boxes = [];
        const ears = [];
        const noseOffsets = [];
        const startedAt = performance.now();
        const challenge = [
            { key: 'closer', label: 'move closer' },
            { key: 'left', label: 'move to the LEFT side of the screen' },
            { key: 'right', label: 'move to the RIGHT side of the screen' },
        ][Math.floor(Math.random() * 3)];
        let singleFace = true;

        setText('[data-scanner-status]', `Live check: blink, then ${challenge.label}.`);
        for (let i = 0; i < 28; i++) {
            await new Promise(resolve => setTimeout(resolve, 260));
            const detections = await faceapi
                .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.55 }))
                .withFaceLandmarks()
                .withFaceDescriptors();

            if (detections.length > 1) {
                singleFace = false;
                break;
            }

            const result = detections[0];
            if (!result) continue;

            const box = result.detection.box;
            const landmarks = result.landmarks;
            samples.push(Array.from(result.descriptor));
            boxes.push(box);
            ears.push(eyeAspectRatio(landmarks));
            noseOffsets.push(noseOffset(landmarks, box));
            setText('[data-face-state]', `${Math.min(samples.length, 8)}/8`);

            const live = buildLiveness(samples, boxes, ears, noseOffsets, startedAt, challenge, singleFace);
            if (samples.length >= 8 && live.blink_detected && live.challenge_passed) break;
        }

        const liveness = buildLiveness(samples, boxes, ears, noseOffsets, startedAt, challenge, singleFace);
        if (samples.length < 6 || !liveness.single_face || !liveness.blink_detected || !liveness.challenge_passed || !liveness.face_movement) {
            setText('[data-face-state]', 'Failed');
            toast('error', 'Live check failed', 'Use your live face only, blink clearly, and follow the movement prompt.');
            throw new Error('Live check failed.');
        }

        state.embedding = samples[samples.length - 1];
        state.liveness = liveness;
        setText('[data-face-state]', 'Checking');
        const payload = await post(api.face, { qr_token: state.qrToken, embedding: state.embedding, liveness: state.liveness });
        setText('[data-face-state]', payload.match ? 'Passed' : 'Failed');
        state.faceVerified = payload.match;
        state.canRegisterFace = !!payload.can_register_face;
        const adminLinks = app.querySelectorAll('[data-admin-link]');
        if (payload.face_registration_url) adminLinks[0]?.setAttribute('href', payload.face_registration_url);
        if (payload.logs_url) adminLinks[1]?.setAttribute('href', payload.logs_url);
        adminLinks.forEach(link => {
            link.classList.toggle('hidden', !state.canRegisterFace);
            link.classList.toggle('inline-flex', state.canRegisterFace);
        });
        app.querySelector('[data-post-qr-panel]').classList.remove('hidden');
        app.querySelector('[data-post-qr-panel]').classList.add('grid');
        setText('[data-stage-title]', 'Choose Action');
        setText('[data-stage-subtitle]', 'Select Time In, Time Out, or Overtime.');
        setText('[data-scanner-status]', 'Face matched. Attendance actions are unlocked.');
        updateActions(payload.status);
        await getGeo();
    }

    function moved(boxes) {
        if (boxes.length < 2) return false;
        return motionScore(boxes) >= 10;
    }

    function buildLiveness(samples, boxes, ears, noseOffsets, startedAt, challenge, singleFace) {
        const earValues = ears.filter(Number.isFinite);
        const minEar = Math.min(...earValues);
        const maxEar = Math.max(...earValues);
        const blinkDetected = earValues.length >= 4 && (maxEar - minEar > 0.055 || minEar < 0.20);
        const sizeVariance = faceSizeVariance(boxes);
        const xDelta = horizontalDelta(boxes, true);
        const challengePassed = challenge.key === 'closer'
            ? sizeVariance >= 9
            : (challenge.key === 'right' ? xDelta >= 10 : xDelta <= -10);

        return {
            face_movement: moved(boxes),
            blink_detected: blinkDetected,
            challenge_passed: challengePassed,
            single_face: singleFace,
            challenge: challenge.key,
            challenge_label: challenge.label,
            samples_count: samples.length,
            elapsed_ms: Math.round(performance.now() - startedAt),
            motion_score: Math.round(motionScore(boxes) * 100) / 100,
            face_size_variance: Math.round(sizeVariance * 100) / 100,
            descriptor_variance: Math.round(descriptorVariance(samples) * 1000000) / 1000000,
        };
    }

    function eyeAspectRatio(landmarks) {
        const left = landmarks.getLeftEye();
        const right = landmarks.getRightEye();
        return (singleEyeRatio(left) + singleEyeRatio(right)) / 2;
    }

    function singleEyeRatio(eye) {
        if (!eye || eye.length < 6) return 0;
        const vertical = distance(eye[1], eye[5]) + distance(eye[2], eye[4]);
        const horizontal = 2 * distance(eye[0], eye[3]);
        return horizontal > 0 ? vertical / horizontal : 0;
    }

    function noseOffset(landmarks, box) {
        const nose = landmarks.getNose();
        const tip = nose?.[3] || nose?.[0];
        if (!tip) return 0;
        return (tip.x - (box.x + box.width / 2)) / Math.max(box.width, 1);
    }

    function motionScore(boxes) {
        if (boxes.length < 2) return 0;
        const first = boxes[0];
        let maxScore = 0;
        boxes.forEach(box => {
            const centerMove = Math.hypot((box.x + box.width / 2) - (first.x + first.width / 2), (box.y + box.height / 2) - (first.y + first.height / 2));
            const sizeMove = Math.abs(box.width - first.width) + Math.abs(box.height - first.height);
            maxScore = Math.max(maxScore, centerMove + sizeMove);
        });
        return maxScore;
    }

    function faceSizeVariance(boxes) {
        return range(boxes.map(box => (box.width + box.height) / 2));
    }

    function horizontalDelta(boxes, mirrored = false) {
        if (boxes.length < 2) return 0;
        const first = boxes[0];
        const last = boxes[boxes.length - 1];
        const rawDelta = (last.x + last.width / 2) - (first.x + first.width / 2);
        return mirrored ? -rawDelta : rawDelta;
    }

    function descriptorVariance(samples) {
        if (samples.length < 2) return 0;
        let total = 0;
        for (let i = 1; i < samples.length; i++) {
            total += Math.sqrt(samples[i].reduce((sum, value, index) => {
                const diff = value - samples[i - 1][index];
                return sum + diff * diff;
            }, 0));
        }
        return total / (samples.length - 1);
    }

    function range(values) {
        const clean = values.filter(Number.isFinite);
        return clean.length ? Math.max(...clean) - Math.min(...clean) : 0;
    }

    function distance(a, b) {
        return Math.hypot(a.x - b.x, a.y - b.y);
    }

    async function getGeo() {
        setText('[data-geo-state]', 'Checking');
        navigator.geolocation.getCurrentPosition(pos => {
            state.geo = { latitude: pos.coords.latitude, longitude: pos.coords.longitude, accuracy: pos.coords.accuracy };
            setText('[data-geo-state]', state.geo.accuracy <= 150 ? 'Passed' : 'Weak');
            setText('[data-location-text]', `${state.geo.latitude.toFixed(6)}, ${state.geo.longitude.toFixed(6)} (${Math.round(state.geo.accuracy)}m)`);
            setText('[data-camera-employee-meta]', `${state.employee.emp_ID} - ${Math.round(state.geo.accuracy)}m accuracy`);
            updateActions(state.status);
            window.dispatchEvent(new CustomEvent('time-entry:geo-ready'));
        }, () => {
            setText('[data-geo-state]', 'Failed');
            setText('[data-location-text]', 'Location permission is required.');
            window.dispatchEvent(new CustomEvent('time-entry:geo-failed'));
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    }

    async function logAction(action) {
        if (!action || !state.faceVerified || !state.embedding || !state.liveness) return;
        if (!state.geo) {
            await new Promise((resolve, reject) => {
                window.addEventListener('time-entry:geo-ready', resolve, { once: true });
                window.addEventListener('time-entry:geo-failed', () => reject(new Error('Location permission is required.')), { once: true });
            });
        }
        const btn = app.querySelector(`[data-action="${action}"]`);
        if (btn) btn.disabled = true;
        try {
            const payload = await post(api.log, {
                qr_token: state.qrToken,
                embedding: state.embedding,
                selected_action: action,
                latitude: state.geo.latitude,
                longitude: state.geo.longitude,
                accuracy: state.geo.accuracy,
                liveness: state.liveness,
                device_info: { platform: navigator.platform, language: navigator.language },
            });
            toast('success', 'Attendance recorded', payload.message);
            setText('[data-stage-title]', 'Recorded');
            setText('[data-stage-subtitle]', `${actionLabel(action)} saved at ${payload.time || 'server time'}.`);
            setText('[data-scanner-status]', `${actionLabel(action)} recorded for ${payload.employee?.name || state.employee.name}.`);
            updateActions(payload.status);
        } catch (error) {
            toast('error', 'Action blocked', error.message || 'Unable to record attendance.');
            updateActions(state.status);
        }
    }

    function stopFace() {
        state.faceStream?.getTracks()?.forEach(track => track.stop());
        state.faceStream = null;
    }

    function canUseCamera() {
        if (window.isSecureContext || ['localhost', '127.0.0.1'].includes(location.hostname)) {
            return true;
        }

        const message = 'Android requires HTTPS for camera and location when using an IP address.';
        setText('[data-stage-title]', 'Secure Connection Required');
        setText('[data-stage-subtitle]', 'Open this page using HTTPS.');
        setText('[data-scanner-status]', message);
        toast('error', 'Camera blocked', message);
        return false;
    }

    app.querySelectorAll('.time-entry-action').forEach(btn => btn.addEventListener('click', () => logAction(btn.dataset.action)));
    app.querySelector('[data-reset]').addEventListener('click', () => location.reload());
    resetActions();
    clock();
    startQr().catch(error => toast('error', 'Scanner unavailable', error.message || 'Camera access failed.'));

    function actionLabel(action) {
        return { time_in: 'Time In', time_out: 'Time Out', overtime: 'Overtime' }[action] || 'Attendance';
    }
})();
</script>
@endpush
