<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رهیار 724</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✈️</text></svg>">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;500;700;900&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --accent: #06b6d4;
            --glow: rgba(99, 102, 241, 0.3);
        }

        html, body {
            width: 100%; height: 100%;
            overflow: hidden;
            font-family: 'Vazirmatn', sans-serif;
        }

        body {
            background: #0a0a1a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* ─── Animated Gradient Background ─── */
        .bg-gradient {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 70% 30%, rgba(99, 102, 241, 0.18) 0%, transparent 50%),
                radial-gradient(ellipse at 20% 80%, rgba(139, 92, 246, 0.14) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 70%, rgba(6, 182, 212, 0.12) 0%, transparent 50%),
                linear-gradient(180deg, #080818 0%, #0d0d2b 50%, #080818 100%);
            animation: bgShift 15s ease-in-out infinite alternate;
        }

        @keyframes bgShift {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(25deg); }
        }

        /* ─── Animated Mesh Grid ─── */
        .mesh {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .mesh-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.06), transparent);
            height: 1px;
            animation: meshH linear infinite;
        }

        .mesh-line:nth-child(1) { top: 20%; animation-duration: 18s; width: 120%; left: -10%; }
        .mesh-line:nth-child(2) { top: 40%; animation-duration: 22s; width: 130%; left: -15%; animation-delay: -5s; background: linear-gradient(90deg, transparent, rgba(6, 182, 212, 0.05), transparent); }
        .mesh-line:nth-child(3) { top: 60%; animation-duration: 16s; width: 110%; left: -5%; animation-delay: -8s; }
        .mesh-line:nth-child(4) { top: 80%; animation-duration: 20s; width: 125%; left: -12%; animation-delay: -3s; background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.05), transparent); }

        .mesh-vline {
            position: absolute;
            background: linear-gradient(180deg, transparent, rgba(99, 102, 241, 0.04), transparent);
            width: 1px;
            animation: meshV linear infinite;
        }

        .mesh-vline:nth-child(5) { left: 25%; animation-duration: 25s; height: 120%; top: -10%; }
        .mesh-vline:nth-child(6) { left: 50%; animation-duration: 20s; height: 130%; top: -15%; animation-delay: -7s; }
        .mesh-vline:nth-child(7) { left: 75%; animation-duration: 22s; height: 115%; top: -7%; animation-delay: -12s; }

        @keyframes meshH {
            0% { transform: translateX(-20%); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateX(20%); opacity: 0; }
        }

        @keyframes meshV {
            0% { transform: translateY(-20%); opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { transform: translateY(20%); opacity: 0; }
        }

        /* ─── Floating Orbs ─── */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.35;
            animation: float 10s ease-in-out infinite;
        }

        .orb-1 {
            width: 350px; height: 350px;
            background: var(--primary);
            top: -120px; left: -80px;
            animation-duration: 12s;
        }

        .orb-2 {
            width: 280px; height: 280px;
            background: var(--accent);
            bottom: -100px; right: -60px;
            animation-delay: -4s;
            animation-duration: 14s;
        }

        .orb-3 {
            width: 180px; height: 180px;
            background: var(--secondary);
            top: 40%; right: 20%;
            animation-delay: -7s;
            animation-duration: 16s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(25px, -25px) scale(1.08); }
            50% { transform: translate(-15px, 15px) scale(0.95); }
            75% { transform: translate(10px, 10px) scale(1.03); }
        }

        /* ─── Sparkle Particles ─── */
        .sparkles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .sparkle {
            position: absolute;
            width: 3px; height: 3px;
            background: #fff;
            border-radius: 50%;
            animation: twinkle linear infinite;
        }

        .sparkle:nth-child(1) { top: 15%; left: 12%; animation-duration: 3s; animation-delay: 0s; }
        .sparkle:nth-child(2) { top: 25%; left: 78%; animation-duration: 4s; animation-delay: -1s; width: 2px; height: 2px; }
        .sparkle:nth-child(3) { top: 45%; left: 35%; animation-duration: 3.5s; animation-delay: -2s; width: 4px; height: 4px; }
        .sparkle:nth-child(4) { top: 65%; left: 88%; animation-duration: 2.8s; animation-delay: -0.5s; }
        .sparkle:nth-child(5) { top: 80%; left: 22%; animation-duration: 4.5s; animation-delay: -3s; width: 2px; height: 2px; }
        .sparkle:nth-child(6) { top: 10%; left: 55%; animation-duration: 3.2s; animation-delay: -1.5s; width: 4px; height: 4px; }
        .sparkle:nth-child(7) { top: 55%; left: 65%; animation-duration: 3.8s; animation-delay: -2.5s; }
        .sparkle:nth-child(8) { top: 35%; left: 8%; animation-duration: 2.5s; animation-delay: -0.8s; width: 2px; height: 2px; }
        .sparkle:nth-child(9) { top: 75%; left: 50%; animation-duration: 4.2s; animation-delay: -3.5s; }
        .sparkle:nth-child(10) { top: 90%; left: 70%; animation-duration: 3s; animation-delay: -1.2s; width: 4px; height: 4px; }
        .sparkle:nth-child(11) { top: 5%; left: 42%; animation-duration: 3.6s; animation-delay: -2.2s; }
        .sparkle:nth-child(12) { top: 50%; left: 92%; animation-duration: 2.9s; animation-delay: -0.3s; width: 2px; height: 2px; }

        @keyframes twinkle {
            0%, 100% { opacity: 0; transform: scale(0); }
            50% { opacity: 1; transform: scale(1); }
        }

        /* ─── Orbiting Ring ─── */
        .orbit-container {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 500px; height: 500px;
            pointer-events: none;
            animation: orbitSpin 60s linear infinite;
        }

        .orbit-ring {
            position: absolute;
            inset: 0;
            border: 1px solid rgba(99, 102, 241, 0.06);
            border-radius: 50%;
        }

        .orbit-ring:nth-child(2) {
            inset: 60px;
            border-color: rgba(6, 182, 212, 0.05);
            animation: orbitSpin 45s linear infinite reverse;
        }

        .orbit-ring:nth-child(3) {
            inset: 120px;
            border-color: rgba(139, 92, 246, 0.04);
            animation: orbitSpin 35s linear infinite;
        }

        .orbit-dot {
            position: absolute;
            width: 6px; height: 6px;
            background: var(--primary);
            border-radius: 50%;
            top: -3px; left: 50%;
            box-shadow: 0 0 12px var(--glow);
        }

        .orbit-ring:nth-child(2) .orbit-dot {
            background: var(--accent);
            box-shadow: 0 0 12px rgba(6, 182, 212, 0.4);
        }

        .orbit-ring:nth-child(3) .orbit-dot {
            width: 4px; height: 4px;
            top: -2px;
            background: var(--secondary);
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.4);
        }

        @keyframes orbitSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ─── Main Content ─── */
        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            animation: fadeInUp 1.2s ease-out;
        }

        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* ─── Logo Container ─── */
        .logo-container {
            position: relative;
            width: 100px; height: 100px;
            margin: 0 auto 35px;
        }

        .logo-ring {
            position: absolute;
            inset: 0;
            border: 2px solid transparent;
            border-top-color: var(--primary);
            border-right-color: var(--accent);
            border-radius: 50%;
            animation: logoSpin 3s linear infinite;
        }

        .logo-ring-inner {
            position: absolute;
            inset: 10px;
            border: 1px solid transparent;
            border-bottom-color: var(--secondary);
            border-left-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: logoSpin 2s linear infinite reverse;
        }

        @keyframes logoSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo-icon {
            position: absolute;
            inset: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon svg {
            width: 100%; height: 100%;
            filter: drop-shadow(0 0 20px var(--glow));
        }

        /* ─── Brand Name ─── */
        .brand-wrapper {
            position: relative;
            display: inline-block;
        }

        .brand-name {
            font-size: clamp(2.8rem, 10vw, 5.5rem);
            font-weight: 900;
            letter-spacing: -3px;
            line-height: 1;
            background: linear-gradient(135deg, #ffffff 0%, #e0e7ff 30%, var(--accent) 70%, var(--primary) 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradientFlow 5s ease-in-out infinite;
        }

        @keyframes gradientFlow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .brand-glow {
            position: absolute;
            inset: -20px -40px;
            background: radial-gradient(ellipse, rgba(99, 102, 241, 0.15), transparent 70%);
            filter: blur(30px);
            z-index: -1;
            animation: glowPulse 3s ease-in-out infinite;
        }

        @keyframes glowPulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.1); }
        }

        /* ─── Divider Lines ─── */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 25px auto 0;
            animation: fadeInUp 1.5s ease-out 0.3s both;
        }

        .divider-line {
            width: 40px; height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary));
            animation: linePulse 3s ease-in-out infinite;
        }

        .divider-line:last-child {
            background: linear-gradient(90deg, var(--primary), transparent);
        }

        .divider-dot {
            width: 5px; height: 5px;
            background: var(--accent);
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(6, 182, 212, 0.5);
            animation: dotPulse 2s ease-in-out infinite;
        }

        @keyframes linePulse {
            0%, 100% { opacity: 0.4; width: 40px; }
            50% { opacity: 1; width: 55px; }
        }

        @keyframes dotPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 8px rgba(6, 182, 212, 0.3); }
            50% { transform: scale(1.5); box-shadow: 0 0 16px rgba(6, 182, 212, 0.6); }
        }

        /* ─── Enter Button ─── */

        /* ─── Corner Accents ─── */
        .corner {
            position: fixed;
            width: 50px; height: 50px;
            z-index: 5;
            pointer-events: none;
            opacity: 0.3;
        }

        .corner::before, .corner::after {
            content: '';
            position: absolute;
            background: var(--primary);
        }

        .corner-tl { top: 24px; left: 24px; }
        .corner-tl::before { top: 0; left: 0; width: 20px; height: 1px; }
        .corner-tl::after { top: 0; left: 0; width: 1px; height: 20px; }

        .corner-tr { top: 24px; right: 24px; }
        .corner-tr::before { top: 0; right: 0; width: 20px; height: 1px; }
        .corner-tr::after { top: 0; right: 0; width: 1px; height: 20px; }

        .corner-bl { bottom: 24px; left: 24px; }
        .corner-bl::before { bottom: 0; left: 0; width: 20px; height: 1px; }
        .corner-bl::after { bottom: 0; left: 0; width: 1px; height: 20px; }

        .corner-br { bottom: 24px; right: 24px; }
        .corner-br::before { bottom: 0; right: 0; width: 20px; height: 1px; }
        .corner-br::after { bottom: 0; right: 0; width: 1px; height: 20px; }

        /* ─── Horizontal Scan ─── */
        .h-scan {
            position: fixed;
            left: 0; width: 100%; height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(6, 182, 212, 0.2) 30%, rgba(99, 102, 241, 0.3) 50%, rgba(6, 182, 212, 0.2) 70%, transparent 100%);
            pointer-events: none;
            z-index: 5;
            animation: hScan 8s linear infinite;
        }

        @keyframes hScan {
            0% { top: -1px; }
            100% { top: 100%; }
        }

        /* ─── Responsive ─── */
        @media (max-width: 768px) {
            .orb { filter: blur(70px); }
            .orb-1 { width: 220px; height: 220px; }
            .orb-2 { width: 180px; height: 180px; }
            .orb-3 { width: 120px; height: 120px; }
            .orbit-container { width: 320px; height: 320px; }
            .orbit-ring:nth-child(2) { inset: 40px; }
            .orbit-ring:nth-child(3) { inset: 80px; }
            .logo-container { width: 80px; height: 80px; margin-bottom: 25px; }
            .corner { width: 30px; height: 30px; }
            .corner::before { width: 14px !important; }
            .corner::after { height: 14px !important; }
            .btn-group { margin-top: 35px; gap: 12px; }
            .enter-btn { padding: 12px 32px; font-size: 0.85rem; }
        }

        @media (max-width: 380px) {
            .brand-name { font-size: 2.5rem; letter-spacing: -1px; }
            .btn-group { margin-top: 28px; flex-direction: column; align-items: center; }
            .enter-btn { width: 80%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="mesh">
        <div class="mesh-line"></div><div class="mesh-line"></div>
        <div class="mesh-line"></div><div class="mesh-line"></div>
        <div class="mesh-vline"></div><div class="mesh-vline"></div>
        <div class="mesh-vline"></div>
    </div>
    <div class="h-scan"></div>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="sparkles">
        <div class="sparkle"></div><div class="sparkle"></div><div class="sparkle"></div>
        <div class="sparkle"></div><div class="sparkle"></div><div class="sparkle"></div>
        <div class="sparkle"></div><div class="sparkle"></div><div class="sparkle"></div>
        <div class="sparkle"></div><div class="sparkle"></div><div class="sparkle"></div>
    </div>

    <div class="orbit-container">
        <div class="orbit-ring"><div class="orbit-dot"></div></div>
        <div class="orbit-ring"><div class="orbit-dot"></div></div>
        <div class="orbit-ring"><div class="orbit-dot"></div></div>
    </div>

    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <div class="content">
        <div class="logo-container">
            <div class="logo-ring"></div>
            <div class="logo-ring-inner"></div>
            <div class="logo-icon">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6366f1"/>
                            <stop offset="50%" stop-color="#8b5cf6"/>
                            <stop offset="100%" stop-color="#06b6d4"/>
                        </linearGradient>
                    </defs>
                    <path d="M50 18 L62 42 L88 42 L67 58 L74 82 L50 68 L26 82 L33 58 L12 42 L38 42 Z" fill="url(#g1)" opacity="0.15"/>
                    <path d="M50 25 L58 42 L78 42 L62 54 L67 72 L50 62 L33 72 L38 54 L22 42 L42 42 Z" fill="url(#g1)" opacity="0.3"/>
                    <path d="M50 32 L55 42 L68 42 L57 50 L61 62 L50 55 L39 62 L43 50 L32 42 L45 42 Z" fill="url(#g1)" opacity="0.6"/>
                    <circle cx="50" cy="48" r="8" fill="url(#g1)" opacity="0.9"/>
                    <circle cx="50" cy="48" r="3" fill="#fff" opacity="0.8"/>
                </svg>
            </div>
        </div>

        <div class="brand-wrapper">
            <div class="brand-glow"></div>
            <h1 class="brand-name">رهیار 724</h1>
        </div>

        <div class="divider">
            <div class="divider-line"></div>
            <div class="divider-dot"></div>
            <div class="divider-line"></div>
        </div>

    </div>
</body>
</html>