<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Description Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }

        .required-label::after {
            content: " *";
            color: #e74c3c;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
            font-style: italic;
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .result-card {
            display: none;
        }

        .result-card.show {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .job-title {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .bullet-list {
            list-style: none;
            padding-left: 0;
        }

        .bullet-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
            line-height: 1.6;
            color: #444;
        }

        .bullet-list li::before {
            content: "●";
            color: #667eea;
            font-size: 20px;
            position: absolute;
            left: 0;
            top: -2px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-secondary {
            background: #95a5a6;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4);
        }

        .empty-message {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Form Card -->
        <div class="card" id="formCard">
            <h2>📝 Job Description Form</h2>
            <form id="jobForm">
                <div class="form-group">
                    <label for="jobTitle" class="required-label">Job Title :</label>
                    <input type="text" id="jobTitle" name="jobTitle" placeholder="e.g. Senior Web Developer" required style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px;">
                </div>

                <div class="form-group">
                    <label for="keyPoint" class="required-label">Job Description :</label>
                    <textarea id="keyPoint" name="keyPoint" rows="8" placeholder="Describe the project, your role, and key achievements...&#10;&#10;แยกแต่ละข้อด้วยการขึ้นบรรทัดใหม่&#10;เช่น:&#10;Developed responsive web applications using React&#10;Led a team of 5 developers&#10;Improved site performance by 40%" required></textarea>
                    <div class="hint">💡 แยกแต่ละหัวข้อด้วยการกด Enter ขึ้นบรรทัดใหม่</div>
                </div>

                <button type="submit">✨ แสดงผลแบบ Bullet Points</button>
            </form>
        </div>

        <!-- Result Card -->
        <div class="card result-card" id="resultCard">
            <h2>📋 ผลลัพธ์</h2>
            <div class="job-title" id="displayTitle"></div>
            <ul class="bullet-list" id="displayPoints"></ul>
            
            <div class="action-buttons">
                <button onclick="editForm()" class="btn-secondary" style="flex: 1;">✏️ แก้ไข</button>
                <button onclick="copyToClipboard()" style="flex: 1;">📋 Copy</button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('jobForm');
        const formCard = document.getElementById('formCard');
        const resultCard = document.getElementById('resultCard');
        const displayTitle = document.getElementById('displayTitle');
        const displayPoints = document.getElementById('displayPoints');

        // ฟังก์ชันแปลง text เป็น bullet list
        function convertToBullets(text) {
            const lines = text.split('\n')
                .map(line => line.trim())
                .filter(line => line !== '');
            
            let html = '';
            lines.forEach(line => {
                // ลบ bullet ที่มีอยู่แล้ว (-, *, •, -)
                line = line.replace(/^[-*•\-]\s*/, '');
                html += `<li>${escapeHtml(line)}</li>`;
            });
            
            return html;
        }

        // ป้องกัน XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Submit Form
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jobTitle = document.getElementById('jobTitle').value;
            const keyPoint = document.getElementById('keyPoint').value;
            
            // แสดงผล
            displayTitle.textContent = jobTitle;
            displayPoints.innerHTML = convertToBullets(keyPoint);
            
            // แสดง result card
            formCard.style.display = 'none';
            resultCard.classList.add('show');
        });

        // กลับไปแก้ไข
        function editForm() {
            resultCard.classList.remove('show');
            formCard.style.display = 'block';
        }

        // Copy to clipboard
        function copyToClipboard() {
            const title = displayTitle.textContent;
            const points = Array.from(displayPoints.querySelectorAll('li'))
                .map(li => '• ' + li.textContent)
                .join('\n');
            
            const fullText = `${title}\n\n${points}`;
            
            navigator.clipboard.writeText(fullText).then(() => {
                alert('✅ คัดลอกไปยัง Clipboard แล้ว!');
            }).catch(err => {
                console.error('Error copying:', err);
            });
        }
    </script>
</body>
</html>