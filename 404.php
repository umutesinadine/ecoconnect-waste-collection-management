<?php header("HTTP/1.0 404 Not Found"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - EcoConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1a936f 0%, #114b5f 100%); min-height: 100vh; display: flex; align-items: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .error-card { background: white; border-radius: 15px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 60px 40px; text-align: center; }
        .error-code { font-size: 8rem; font-weight: 900; color: #2e7d32; line-height: 1; margin-bottom: 20px; }
        .error-icon { font-size: 4rem; color: #ef5350; margin-bottom: 20px; }
        h2 { color: #333; margin-bottom: 15px; }
        p { color: #666; margin-bottom: 30px; }
        .btn-home { background: linear-gradient(135deg, #2e7d32, #1a936f); border: none; color: white; padding: 12px 30px; border-radius: 10px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(26,147,111,0.4); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="error-card">
                    <div class="error-icon"><i class="fas fa-map-signs"></i></div>
                    <div class="error-code">404</div>
                    <h2>Page Not Found</h2>
                    <p>The page you are looking for does not exist or has been moved.<br>Please check the URL or navigate back to the homepage.</p>
                    <a href="index.php" class="btn-home"><i class="fas fa-home me-2"></i>Go to Homepage</a>
                    <button onclick="history.back()" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Go Back</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>