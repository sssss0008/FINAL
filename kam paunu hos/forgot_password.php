<?php
// forgot_password.php
// Simple placeholder page to inform users the "Forgot Password" feature is in progress.
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot Password — Work in Progress</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root{
            --bg1:#6a11cb;
            --bg2:#2575fc;
            --card:#ffffffef;
            --accent:#ffb86b;
            --muted:#555;
        }
        html,body{height:100%;margin:0;font-family:Inter,Segoe UI,Roboto,Arial,sans-serif;background:linear-gradient(135deg,var(--bg1),var(--bg2));color:#222}
        .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:32px}
        .card{background:var(--card);border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.15);max-width:720px;width:100%;padding:28px;display:flex;gap:20px;align-items:center}
        .left{flex:1}
        h1{margin:0 0 8px 0;font-size:20px;color:#111}
        p{margin:0 0 12px 0;color:var(--muted);line-height:1.45}
        .badge{display:inline-block;background:linear-gradient(90deg,var(--accent),#ff7ab6);color:#111;padding:6px 10px;border-radius:999px;font-weight:600;font-size:13px;margin-bottom:10px}
        .status{display:flex;align-items:center;gap:12px;margin-top:6px}
        .spinner{width:56px;height:56px;border-radius:50%;background:
            conic-gradient(#fff0 10% , #ffffff70 10% 30%, #ffffff22 30% 100%);display:flex;align-items:center;justify-content:center;position:relative;box-shadow:inset 0 -8px 18px rgba(255,255,255,.08)}
        .spinner::after{content:'';width:34px;height:34px;border-radius:50%;background:linear-gradient(180deg,#ffffff,#ffffff00)}
        .progress-text{font-size:14px;color:#333}
        .contact{margin-top:14px;font-size:13px;color:var(--muted)}
        a.contact-link{color:var(--bg2);text-decoration:none;font-weight:600}
        @media(max-width:520px){
            .card{flex-direction:column;align-items:flex-start}
            .spinner{width:46px;height:46px}
        }
        /* subtle animation */
        @keyframes rotate{to{transform:rotate(360deg)}}
        .spinner{animation:rotate 1.8s linear infinite}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card" role="status" aria-live="polite">
            <div class="left">
                <div class="badge">Feature: Forgot Password</div>
                <h1>We're working on it</h1>
                <p>
                    The "Forgot Password" feature is currently under development. We appreciate your patience while we build a secure and reliable reset flow.
                </p>
                <div class="status">
                    <div class="spinner" aria-hidden="true"></div>
                    <div>
                        <div class="progress-text">Status: In development</div>
                        <div class="contact">Questions? Email <a class="contact-link" href="mailto:awiskaracharya@gmail.com">awiskaracharya@gmail.com</a></div>
                    </div>
                </div>
            </div>
            <div style="min-width:140px;text-align:center;">
                <svg width="96" height="96" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="g" x1="0" x2="1">
                            <stop offset="0" stop-color="#fff"/>
                            <stop offset="1" stop-color="#ffffff30"/>
                        </linearGradient>
                    </defs>
                    <rect x="1.5" y="6" width="21" height="12" rx="2.5" fill="url(#g)" opacity=".12"/>
                    <path d="M16 11c0-2-1.5-3.5-4-3.5S8 9 8 11" stroke="#fff" stroke-opacity=".9" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9.5 13v1.2" stroke="#fff" stroke-opacity=".9" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
                <div style="font-size:12px;color:#444;margin-top:8px">Thank you for your patience</div>
            </div>
        </div>
    </div>
</body>
</html>