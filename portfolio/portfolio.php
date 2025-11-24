<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
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
        die("User not found.");
    }

    // Query 2: Profile
    $stmt = $conn->prepare("SELECT professionalTitle, phone, facebook, facebookUrl,
            logoImage, profileImage, coverImage, introContent, skillsContent
        FROM profile WHERE userID = :userID");
    $stmt->execute(['userID' => $userID]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Merge user + profile
    $data = array_merge($userData, $profileData ?: []);

    // Full path images (Profile) -> ใช้ Logic เช็ค Path เพื่อป้องกัน Path ซ้อน
    foreach (['logoImage', 'profileImage', 'coverImage'] as $img) {
        if (!empty($data[$img])) {
            // เช็คว่ามี / นำหน้า หรือเป็น http อยู่แล้วหรือยัง ถ้ายังค่อยเติม
            if (strpos($data[$img], '/') !== 0 && strpos($data[$img], 'http') !== 0) {
                $data[$img] = "/upload/{$userID}/" . $data[$img];
            }
        }
    }

    //  Query 3: เพิ่ม s.skillsUrl เพื่อดึง Class ไอคอน
    $stmt = $conn->prepare("SELECT s.skillsID, s.skillsName, s.skillsUrl 
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

    // Query 6: Projects (ใช้ JSON_OBJECT และ GROUP BY ให้ครบ)
    $sql = "SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder, 
                   JSON_ARRAYAGG(
                       JSON_OBJECT('name', s.skillsName, 'url', s.skillsUrl)
                   ) AS skills
            FROM project AS p
            LEFT JOIN projectSkill ps ON p.projectID = ps.projectID
            LEFT JOIN skills s ON ps.skillsID = s.skillsID
            WHERE p.userID = :userID
            GROUP BY p.projectID, p.projectTitle, p.projectImage, p.keyPoint, p.sortOrder
            ORDER BY p.sortOrder ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['userID' => $userID]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process Project Data
    foreach ($projects as &$item) {
        // เช็ค Path รูปภาพ ไม่ให้เติมซ้ำหากมี path อยู่แล้ว
        if (!empty($item['projectImage'])) {
            if (strpos($item['projectImage'], '/') !== 0 && strpos($item['projectImage'], 'http') !== 0) {
                $item['projectImage'] = "/upload/{$userID}/" . $item['projectImage'];
            }
        }

        // Decode JSON Object
        $item['skillsArray'] = [];
        if (!empty($item['skills'])) {
            $decoded = json_decode($item['skills'], true);
            if (is_array($decoded)) {
                // กรองค่าที่ name เป็น null ออก
                $item['skillsArray'] = array_filter($decoded, function ($k) {
                    return !empty($k['name']);
                });
            }
        }
    }
    unset($item); // ป้องกันบั๊กจากการใช้ reference

    // Helper functions
    function formatLineBreaks($text)
    {
        if (empty($text)) return '';
        return nl2br(htmlspecialchars($text));
    }

    function formatDate($date)
    {
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
            <?php foreach ($selectedSkills as $skill):
              $skillName = htmlspecialchars($skill['skillsName']);
              // ใช้ Class จาก DB ถ้าไม่มีให้ใช้ default
              $iconClass = !empty($skill['skillsUrl']) ? htmlspecialchars($skill['skillsUrl']) : 'fas fa-code';
            ?>
              <div class="skill-item">
                <div class="skill-icon">
                  <i class="<?= $iconClass ?>"></i>
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
                    <?php foreach ($proj['skillsArray'] as $skillObj):
                      $sName = htmlspecialchars($skillObj['name']);
                      // ถ้าไม่มี URL icon ให้ใช้รูป Code แทน
                      $sUrl  = !empty($skillObj['url']) ? htmlspecialchars($skillObj['url']) : 'fas fa-code';
                    ?>
                      <span class="tech-item" title="<?= $sName ?>">
                        <i class="<?= $sUrl ?>"></i>
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
                  <li><strong>Company:</strong> <?= htmlspecialchars($work['companyName']) ?></li>
                  <li><strong>Position:</strong> <?= htmlspecialchars($work['position']) ?></li>
                  <?php if (!empty($work['jobDescription'])): ?>
                    <?php foreach (explode("\n", $work['jobDescription']) as $desc):
                      if (trim($desc)): ?>
                        <li><?= htmlspecialchars(trim($desc)) ?></li>
                    <?php endif;
                    endforeach; ?>
                  <?php endif; ?>
                  <?php if (!empty($work['remark'])): ?>
                    <li><em><?= htmlspecialchars($work['remark']) ?></em></li>
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
                  <li><strong><?= htmlspecialchars($edu['educationName']) ?></strong></li>
                  <li>Faculty: <?= htmlspecialchars($edu['facultyName']) ?></li>
                  <li>Major: <?= htmlspecialchars($edu['majorName']) ?></li>
                  <?php if (!empty($edu['remark'])): ?>
                    <li><em><?= htmlspecialchars($edu['remark']) ?></em></li>
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