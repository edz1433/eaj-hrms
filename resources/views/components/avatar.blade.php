@props(['profile' => null, 'fname' => '', 'lname' => ''])

@php
    $i1       = strtoupper(substr($fname ?? '', 0, 1));
    $i2       = strtoupper(substr($lname  ?? '', 0, 1));
    $initials = ($i1 . $i2) ?: '?';
    $palette = ['#C9407A','#7C3AED','#2563EB','#059669','#D97706','#DC2626','#0891B2','#0D9488'];
    $bg = $i1 ? $palette[ord($i1) % count($palette)] : '#C9407A';
    $profileFile = trim((string) $profile);
    $placeholderFiles = ['default.png', 'default-male.png', 'default-female.png'];
    $hasImg = $profileFile
        && !in_array(strtolower($profileFile), $placeholderFiles, true)
        && file_exists(public_path('Profile/Employee/'.$profileFile));
@endphp

@if($hasImg)
    <img src="{{ asset('Profile/Employee/'.$profileFile) }}"
         {{ $attributes->merge(['alt' => $initials]) }}>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center font-bold text-white']) }}
          style="background-color:{{ $bg }}">
        {{ $initials }}
    </span>
@endif
