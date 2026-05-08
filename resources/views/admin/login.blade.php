<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin · SETQ.AI</title>
<style>
  body { background: #0a0a14; color: #e8e8ee; font-family: -apple-system, BlinkMacSystemFont, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
  .box { background: #11111c; border: 1px solid #1f1f30; padding: 32px; border-radius: 14px; min-width: 320px; }
  h1 { font-size: 18px; font-weight: 800; margin: 0 0 16px; letter-spacing: -0.3px; }
  input { display: block; width: 100%; box-sizing: border-box; padding: 12px 14px; background: #181828; border: 1px solid #1f1f30; color: #e8e8ee; font-size: 14px; border-radius: 8px; margin-bottom: 12px; outline: none; }
  input:focus { border-color: #4dd4ff; }
  button { width: 100%; padding: 12px; background: #4dd4ff; color: #000; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; }
  .err { color: #ff6666; font-size: 12px; margin-bottom: 12px; }
</style>
</head>
<body>
<div class="box">
  <h1>Admin · SETQ.AI</h1>
  @if(!empty($error))
    <div class="err">Wrong password.</div>
  @endif
  <form method="POST">
    @csrf
    <input type="password" name="password" placeholder="Password" autofocus required>
    <button type="submit">Sign in</button>
  </form>
</div>
</body>
</html>
