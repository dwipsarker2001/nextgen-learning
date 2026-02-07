<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coming Soon | NextGen Learning</title>
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <!-- Bootstrap CDN for styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Heebo', sans-serif;
      background: linear-gradient(135deg, #f3f4f6, #e0e7ff);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    .coming-soon-container {
      text-align: center;
      background: #ffffff;
      padding: 50px 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      max-width: 600px;
      width: 90%;
    }

    .coming-soon-container h1 {
      font-size: 3rem;
      font-weight: 700;
      color: #1e3a8a;
    }

    .coming-soon-container p {
      font-size: 1.1rem;
      color: #374151;
      margin: 20px 0 30px;
    }

    .progress {
      height: 20px;
      border-radius: 10px;
      background-color: #e5e7eb;
      overflow: hidden;
    }

    .progress-bar {
      background: linear-gradient(90deg, #4f46e5, #3b82f6);
      font-weight: 500;
    }

    .subscribe-input {
      max-width: 400px;
      margin: 0 auto;
    }

    .subscribe-input input {
      border-radius: 10px 0 0 10px;
      border: 1px solid #d1d5db;
    }

    .subscribe-input button {
      border-radius: 0 10px 10px 0;
      background: #4f46e5;
      color: #fff;
      border: none;
    }

    .illustration {
      margin-top: 30px;
      max-width: 100%;
      height: auto;
    }

    @media (max-width: 576px) {
      .coming-soon-container h1 {
        font-size: 2.2rem;
      }
    }
  </style>
</head>
<body>

  <div class="coming-soon-container">
    <h1>Coming Soon</h1>
    <p>Our new feature is under development. Stay tuned for updates!</p>
  </div>

</body>
</html>
