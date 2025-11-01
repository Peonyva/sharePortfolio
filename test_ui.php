<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FrostLink - ย่อลิงก์ & QR Code</title>

  <style>
    /* ====== พื้นหลังโทนหิมะน้ำแข็ง ====== */
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(180deg, #F0F9FF 0%, #E0F2FE 100%);
      color: #0F172A;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    /* ====== กล่องหลักแบบ Glass ====== */
    .container {
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
      padding: 40px 50px;
      width: 90%;
      max-width: 480px;
      text-align: center;
      border: 1px solid #E0F2FE;
    }

    h1 {
      font-size: 1.8rem;
      color: #1E3A8A;
      margin-bottom: 10px;
    }

    p.subtitle {
      color: #334155;
      font-size: 0.95rem;
      margin-bottom: 30px;
    }

    /* ====== ช่องกรอกลิงก์ ====== */
    input[type="url"] {
      width: 100%;
      padding: 14px 16px;
      border: 1px solid #BAE6FD;
      border-radius: 10px;
      font-size: 1rem;
      background-color: rgba(255,255,255,0.8);
      outline: none;
      transition: 0.3s;
    }

    input[type="url"]:focus {
      border-color: #38BDF8;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.3);
    }

    /* ====== ปุ่มหลัก ====== */
    button {
      margin-top: 20px;
      width: 100%;
      padding: 14px;
      font-size: 1.05rem;
      font-weight: 600;
      color: #fff;
      background: linear-gradient(90deg, #60A5FA, #38BDF8);
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(90deg, #38BDF8, #0EA5E9);
      transform: scale(1.02);
    }

    /* ====== พื้นที่แสดงผลลิงก์และ QR ====== */
    .result {
      margin-top: 30px;
      padding: 20px;
      border-radius: 12px;
      background: rgba(255,255,255,0.5);
      border: 1px solid #E0F2FE;
      display: none;
    }

    .result a {
      display: inline-block;
      margin-top: 10px;
      color: #1D4ED8;
      text-decoration: none;
      font-weight: 600;
    }

    .result a:hover {
      text-decoration: underline;
    }

    img.qr {
      margin-top: 15px;
      width: 120px;
      height: 120px;
      border-radius: 10px;
    }

    footer {
      margin-top: 40px;
      font-size: 0.8rem;
      color: #64748B;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>❄️ FrostLink</h1>
    <p class="subtitle">ย่อลิงก์ของคุณและสร้าง QR Code ได้ทันที</p>

    <input type="url" id="longUrl" placeholder="วางลิงก์ของคุณที่นี่..." />
    <button id="shortenBtn">ย่อลิงก์ & สร้าง QR</button>

    <div class="result" id="resultBox">
      <p>🔗 ลิงก์ที่ย่อแล้ว:</p>
      <a id="shortUrl" href="#" target="_blank"></a>
      <div id="qrBox">
        <img class="qr" id="qrImage" src="" alt="QR Code" />
      </div>
    </div>

    <footer>© 2025 FrostLink. All rights reserved.</footer>
  </div>

  <script>
    // === ตัวอย่างสคริปต์จำลองการย่อลิงก์ & สร้าง QR ===
    document.getElementById("shortenBtn").addEventListener("click", () => {
      const url = document.getElementById("longUrl").value.trim();
      const resultBox = document.getElementById("resultBox");
      const shortUrl = document.getElementById("shortUrl");
      const qrImage = document.getElementById("qrImage");

      if (!url) {
        alert("กรุณากรอกลิงก์ก่อน");
        return;
      }

      // สมมุติสร้างลิงก์สั้น (จริงๆ ควรใช้ API)
      const fakeShort = "https://frost.li/" + Math.random().toString(36).substring(2, 8);

      // แสดงผล
      shortUrl.textContent = fakeShort;
      shortUrl.href = fakeShort;

      // ใช้ API ฟรีสำหรับ QR (ตัวอย่าง)
      qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(fakeShort)}`;

      resultBox.style.display = "block";
    });
  </script>
</body>
</html>
