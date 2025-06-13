<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كلية العلوم القانونية والسياسية سطات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }
        .card-container {
            width: 80%;
            max-width: 600px;
            margin-bottom: 20px;
        }
        .login-container {
            max-width: 100%;
            width: 400px;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 12px;
        }
        .admin-notice {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #dc3545;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: bold;
            border: 2px solid rgba(220, 53, 69, 0.2);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Title and Card at the top -->
    <h3>كلية العلوم القانونية والسياسية سطات</h3>

    <div class="card">
        <div class="card-header">
            دليل الاستعمال
        </div>
        <div class="card-body">
            <p>خطوات تسجيل الدخول :</p>
            <ol>
                <li>ادخل رقم الأبوجي APOGEE</li>
                <li>ادخل تاريخ الازدياد على الشكل التالي السنة/الشهر/اليوم, مثال (25/02/1999)</li>
                <li>اضغط على زر "الدخول"</li>
            </ol>
        </div>
    </div>

    <!-- Login Card -->
    <div class="login-container">
        <!-- Admin Notice -->
        <div class="admin-notice">
            🛡️ للمسؤولين: استخدم الرقم 16005333 للوصول إلى لوحة الإدارة
            <br><small>Admin: Use code 16005333 with birthdate 06/04/1987</small>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <div style="display: flex; justify-content: space-between;">
                    <label class="form-label">رقم أبوجي :</label>
                    <label for="apogee" class="form-label">: Num APPOGEE</label>
                </div>
                <input type="text" class="form-control" id="apogee" name="apogee" required value="{{ old('apogee') }}">
            </div>
            <div class="mb-3">
                <div style="display: flex; justify-content: space-between;">
                    <label class="form-label">تاريخ الازدياد:</label>
                    <label for="birthdate" class="form-label">: Date de naissance</label>
                </div>
                <input type="date" class="form-control" id="birthdate" name="birthdate" required value="{{ old('birthdate') }}">
            </div>
            <button type="submit" class="btn btn-primary w-100">دخول</button>
        </form>
    </div>

    <div class="container-fluid d-flex justify-content-center align-items-center">
        <h6 class="display-9 py-4 text-center">كلية العلوم القانونية والسياسية سطات - 2024</h6>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
