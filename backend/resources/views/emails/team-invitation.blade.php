<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:Inter,sans-serif;background:#f8fafc;color:#1e293b;margin:0;padding:40px 20px}.card{background:#fff;border-radius:12px;padding:40px;max-width:500px;margin:0 auto;box-shadow:0 1px 3px rgba(0,0,0,.1)}.logo{font-size:24px;font-weight:700;color:#6366f1;margin-bottom:32px}.btn{display:inline-block;background:#6366f1;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;margin:24px 0}.muted{color:#64748b;font-size:14px}</style></head>
<body>
<div class="card">
    <div class="logo">Meridian</div>
    <h2>You've been invited!</h2>
    <p>You've been invited to join <strong>{{ $teamName }}</strong> on Meridian as a <strong>{{ $role }}</strong>.</p>
    <a href="{{ $acceptUrl }}" class="btn">Accept Invitation</a>
    <p class="muted">This invitation expires on {{ $expiresAt }}. If you didn't expect this invitation, you can ignore this email.</p>
</div>
</body>
</html>
