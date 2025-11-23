<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// ตรวจสอบว่ามีค่า userID ส่งมาหรือไม่
$userID = isset($_GET['user']) ? intval($_GET['user']) : 0;

if ($userID <= 0) {
    die("Invalid User ID");
}

try {

    // Query 1: User
    $stmt = $conn->prepare("SELECT userID, firstname, lastname, birthdate, email FROM user WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        die("User not found."); // หยุดทำงานถ้าไม่เจอ User
    }

    // Query 2: Profile
    $stmt = $conn->prepare("SELECT professionalTitle, phone, facebook, facebookUrl,
            logoImage, profileImage, coverImage, introContent, skillsContent
        FROM profile WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);


    // Merge user + profile
    $data = array_merge($userData, $profileData ?: []);

    // Full path images (Profile)
    foreach (['logoImage', 'profileImage', 'coverImage'] as $img) {
        if (!empty($data[$img])) {
            $data[$img] = "/uploads/{$userID}/" . $data[$img];
        }
    }

    // Query 3: ProfileSkill
    $stmt = $conn->prepare("SELECT s.skillsID, s.skillsName 
        FROM profileskill ps
        INNER JOIN skills s ON s.skillsID = ps.skillsID
        WHERE ps.userID = :userID
        ORDER BY s.skillsName ASC");
    $stmt->execute(['userID' => $userID]);
    $selectedSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query 4: Work Experience
    $stmt = $conn->prepare("SELECT * FROM workexperience WHERE userID = :userID ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $userID]);
    $workExperience = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query 5: Education
    $stmt = $conn->prepare("SELECT * FROM education WHERE userID = :userID ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $userID]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query 6: Project
    $sql = "SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder, 
                   JSON_ARRAYAGG(s.skillsName) AS skills
            FROM project AS p
            LEFT JOIN projectSkill ps ON p.projectID = ps.projectID
            LEFT JOIN skills s ON ps.skillsID = s.skillsID
            WHERE p.userID = :userID
            GROUP BY p.projectID
            ORDER BY p.sortOrder ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['userID' => $userID]);
    
    // ✅ แก้ไข 1: เปลี่ยนชื่อตัวแปรเป็น $projects (มี s) เพื่อสื่อว่าเป็นอาเรย์หลายชิ้น
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process Project Data
    foreach ($projects as &$item) {
        // ✅ แก้ไข 2: ใส่ Path รูปภาพ
        if (!empty($item['projectImage'])) {
            $item['projectImage'] = "/uploads/{$userID}/" . $item['projectImage'];
        }

        // ✅ แก้ไข 3: แปลง JSON String จาก Database ให้เป็น Array ของ PHP
        $item['skillsArray'] = [];
        if (!empty($item['skills'])) {
            $decoded = json_decode($item['skills'], true);
            if (is_array($decoded)) {
                // กรองค่า null ออก (กรณี Left Join แล้วไม่เจอ skill มันอาจจะได้ [null])
                $item['skillsArray'] = array_filter($decoded); 
            }
        }
    }
    unset($item); // Break reference

    // Helper functions
    function formatLineBreaks($text) {
        if (empty($text)) return '';
        return nl2br(htmlspecialchars($text));
    }

    function formatDate($date) {
        if (empty($date)) return '';
        return date('M Y', strtotime($date));
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($data['firstname'] ?? 'My') . ' ' . htmlspecialchars($data['lastname'] ?? 'Portfolio') ?></title>
  <script src="https://kit.fontawesome.com/92f0aafca7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/styles/portfolio.css" />
</head>

<body>
  <header class="header">
    <div class="container">
      <nav class="nav-container">
        <div class="logo">
          <?php if (!empty($data['logoImage'])): ?>
            <img src="<?= htmlspecialchars($data['logoImage']); ?>" alt="Logo" />
          <?php endif; ?>
          My Portfolio
        </div>
        <ul class="nav-menu" id="nav-menu">
          <li><a href="#aboutme" class="nav-link active">About Me</a></li>
          <li><a href="#skills" class="nav-link">Skills</a></li>
          <li><a href="#projects" class="nav-link">Projects</a></li>
          <li><a href="#experience" class="nav-link">Work Experience</a></li>
          <li><a href="#education" class="nav-link">Education</a></li>
        </ul>
        <div class="hamburger" id="hamburger">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </nav>
    </div>
  </header>
  <div class="container">
    <section class="hero" id="aboutme">
      <div class="cover-photo">
        <?php if (!empty($data['coverImage'])): ?>
          <img src="<?= htmlspecialchars($data['coverImage']); ?>" alt="cover photo">
        <?php endif; ?>
      </div>

      <div class="profile">
        <div class="profile-content">
          <div class="profile-image">
            <?php if (!empty($data['profileImage'])): ?>
              <img src="<?= htmlspecialchars($data['profileImage']); ?>" alt="Profile Image" />
            <?php endif; ?>
          </div>
          <div class="profile-info">
            <h1><?= htmlspecialchars($data['firstname'] ?? '') . ' ' . htmlspecialchars($data['lastname'] ?? '') ?></h1>
            <p class="subtitle"><?= htmlspecialchars($data['professionalTitle'] ?? '') ?></p>
          </div>
          <div class="divider"></div>
          <div class="contact-info">
            <?php if (!empty($data['email'])): ?>
              <a href="mailto:<?= htmlspecialchars($data['email']); ?>" target="_blank" class="contact-item">
                <i class="fas fa-envelope"></i><?= htmlspecialchars($data['email']); ?>
              </a>
            <?php endif; ?>

            <?php if (!empty($data['phone'])): ?>
              <a href="tel:<?= htmlspecialchars($data['phone']); ?>" class="contact-item">
                <i class="fas fa-phone"></i><?= htmlspecialchars($data['phone']); ?>
              </a>
            <?php endif; ?>

            <?php if (!empty($data['facebookUrl'])): ?>
              <a href="<?= htmlspecialchars($data['facebookUrl']); ?>" target="_blank" class="contact-item">
                <i class="fab fa-facebook"></i><?= htmlspecialchars($data['facebook'] ?? 'Facebook'); ?>
              </a>
            <?php endif; ?>

            <a href="#" class="share-btn" target="_blank"><i class="fas fa-share"></i>Share Profile</a>
          </div>
        </div>
      </div>
    </section>
    <main>
      <section>
        <h2>Intro</h2>
        <div class="intro-content">
          <p><?= formatLineBreaks($data['introContent'] ?? '') ?></p>
        </div>
      </section>

      <section id="skills">
        <h2>Skills</h2>
        <?php if (!empty($data['skillsContent'])): ?>
          <p><?= formatLineBreaks($data['skillsContent']) ?></p>
        <?php endif; ?>

        <div class="skills-grid">
          <?php if (!empty($selectedSkills)): ?>
            <?php
            $skillIcons = [
              'PHP' => '<i class="fab fa-php skill-icon icon-php"></i>',
              'JavaScript' => '<i class="fab fa-js-square skill-icon icon-javascript"></i>',
              'HTML' => '<i class="fab fa-html5 skill-icon icon-html"></i>',
              'CSS' => '<i class="fab fa-css3-alt skill-icon icon-css"></i>',
              'MySQL' => '<i class="fas fa-database skill-icon icon-mysql"></i>',
              'Figma' => '<img src="image/figma.png" alt="Figma" style="width: 40px; height: 40px;">',
            ];

            foreach ($selectedSkills as $skill):
              $skillName = htmlspecialchars($skill['skillsName']);
              $icon = $skillIcons[$skillName] ?? '<i class="fas fa-code skill-icon"></i>';
            ?>
              <div class="skill-item">
                <div class="skill-icon">
                  <?= $icon ?>
                </div>
                <div class="skill-name"><?= $skillName ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>

      <section class="projects" id="projects">
        <h2>Projects</h2>
        <?php if (!empty($projects)): ?>
          <?php foreach ($projects as $proj): ?>
            <div class="project-card">
              <?php if (!empty($proj['projectImage'])): ?>
                <img src="<?= htmlspecialchars($proj['projectImage']) ?>" alt="<?= htmlspecialchars($proj['projectTitle']) ?>" />
              <?php endif; ?>

              <div class="project-content">
                <h3><?= htmlspecialchars($proj['projectTitle']) ?></h3>

                <?php if (!empty($proj['keyPoint'])): ?>
                  <?php
                  $keyPoints = explode("\n", $proj['keyPoint']);
                  if (count($keyPoints) > 0):
                  ?>
                    <ul>
                      <?php foreach ($keyPoints as $point): ?>
                        <?php if (trim($point)): ?>
                          <li><?= htmlspecialchars(trim($point)) ?></li>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($proj['skillsArray'])): ?>
                  <div class="tech-grid">
                    <?php foreach ($proj['skillsArray'] as $skillName): ?>
                      <span class="tech-item">
                        <?php
                        $trimmedSkill = trim($skillName);
                        if ($trimmedSkill === 'PHP') echo '<i class="fab fa-php icon-php"></i>';
                        elseif ($trimmedSkill === 'HTML') echo '<i class="fab fa-html5 icon-html"></i>';
                        elseif ($trimmedSkill === 'CSS') echo '<i class="fab fa-css3-alt icon-css"></i>';
                        elseif ($trimmedSkill === 'JavaScript') echo '<i class="fab fa-js-square icon-javascript"></i>';
                        elseif ($trimmedSkill === 'MySQL') echo '<i class="fas fa-database icon-mysql"></i>';
                        else echo '<i class="fas fa-code"></i>';
                        ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No projects available yet.</p>
        <?php endif; ?>
      </section>

      <section class="experience" id="experience">
        <h2>Work Experience</h2>

        <?php if (!empty($workExperience)): ?>
          <?php foreach ($workExperience as $work): ?>
            <div class="timeline-card">
              <div class="timeline-item">
                <div class="title">
                  <?= formatDate($work['startDate']) ?> —
                  <?= $work['isCurrent'] ? 'Present' : formatDate($work['endDate']) ?>
                  · <?= htmlspecialchars($work['employeeType']) ?>
                </div>
                <ul>
                  <li>Company: <?= htmlspecialchars($work['companyName']) ?></li>
                  <li>Position: <?= htmlspecialchars($work['position']) ?></li>

                  <?php if (!empty($work['jobDescription'])): ?>
                    <?php
                    $descriptions = explode("\n", $work['jobDescription']);
                    foreach ($descriptions as $desc):
                      if (trim($desc)):
                    ?>
                        <li><?= htmlspecialchars(trim($desc)) ?></li>
                    <?php
                      endif;
                    endforeach;
                    ?>
                  <?php endif; ?>

                  <?php if (!empty($work['remark'])): ?>
                    <li><?= htmlspecialchars($work['remark']) ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No work experience added yet.</p>
        <?php endif; ?>
      </section>

      <section class="education" id="education">
        <h2>Education</h2>

        <?php if (!empty($education)): ?>
          <?php foreach ($education as $edu): ?>
            <div class="timeline-card">
              <div class="timeline-item">
                <div class="title">
                  <?= formatDate($edu['startDate']) ?> –
                  <?= $edu['isCurrent'] ? 'Present' : formatDate($edu['endDate']) ?>
                  · <?= htmlspecialchars($edu['degree']) ?>
                </div>
                <ul>
                  <li><?= htmlspecialchars($edu['educationName']) ?></li>
                  <li>Faculty: <?= htmlspecialchars($edu['facultyName']) ?></li>
                  <li>Major: <?= htmlspecialchars($edu['majorName']) ?></li>

                  <?php if (!empty($edu['remark'])): ?>
                    <li><?= htmlspecialchars($edu['remark']) ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No education history added yet.</p>
        <?php endif; ?>
      </section>

    </main>
  </div>

  <footer class="footer">
    <div class="container">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($data['firstname'] ?? '') . ' ' . htmlspecialchars($data['lastname'] ?? '') ?>. All rights reserved.</p>
    </div>
  </footer>

  <script src="/portfolio/portfolio.js"></script>
</body>

</html>