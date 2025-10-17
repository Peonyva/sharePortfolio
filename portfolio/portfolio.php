<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$currentUserID = $_GET['user'] ?? null;
$userID = $currentUserID;

if (empty($currentUserID)) {
    header('Location: /login.php');
    exit; // Always exit after a header redirect
}

try {
  // ดึงข้อมูล Profile + Skills
  $stmt = $conn->prepare(" SELECT p.firstname, p.lastname, p.position, p.email, p.phone, p.facebook,
             p.facebookUrl, p.logoImage, p.profileImage, p.coverImage, 
             p.introContent, p.skillsContent, s.skillsName, s.skillsUrl
      FROM profile p
      LEFT JOIN profileSkill ps ON p.userID = ps.userID
      LEFT JOIN skills s ON ps.skillsID = s.skillsID
      WHERE p.userID = :userID");
  $stmt->execute(['userID' => $userID]);
  $profileSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // ✅ ข้อมูล profile (เอาแถวแรกเท่านั้น)
  $profile = !empty($profileSkills) ? $profileSkills[0] : null;

  // ดึงข้อมูล Work Experience
  $stmt = $conn->prepare("
      SELECT id, companyName, position, jobDescription, employeeType, startDate, endDate, IsCurrent, remarks
      FROM workexperience
      WHERE userID = :userID
      ORDER BY sortOrder ASC
  ");
  $stmt->execute(['userID' => $userID]);
  $workExperience = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // ดึงข้อมูล Education
  $stmt = $conn->prepare("
      SELECT id, educationName, degree, facultyName, majorName, startDate, endDate, IsCurrent, remarks
      FROM education
      WHERE userID = :userID
      ORDER BY sortOrder ASC
  ");
  $stmt->execute(['userID' => $userID]);
  $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // ดึงข้อมูล Project + Skills
  $stmt = $conn->prepare("
      SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, s.skillsUrl
      FROM project p
      LEFT JOIN project_skill ps ON p.projectID = ps.projectID
      LEFT JOIN skills s ON ps.skillsID = s.skillsID
      WHERE p.userID = :userID
  ");
  $stmt->execute(['userID' => $userID]);
  $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Portfolio</title>
  <script src="https://kit.fontawesome.com/92f0aafca7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="styles/index.css" />
</head>

<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <nav class="nav-container">
        <?php if (!empty($profile)): ?>
          <div class="logo">
            <img src="<?= htmlspecialchars($profile['logoImage']); ?>" alt="Logo" />My Portfolio
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
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <div class="container">
    <!-- Hero Section -->
    <section class="hero" id="aboutme">
      <div class="cover-photo">
        <img src="<?= htmlspecialchars($profile['coverImage']); ?>" alt="Cover Photo" />
      </div>

      <div class="profile">
        <div class="profile-content">
          <div class="profile-image">
            <img src="<?= htmlspecialchars($profile['profileImage']); ?>" alt="Profile Image" />
          </div>
          <div class="profile-info">
            <h1><?= htmlspecialchars($profile['firstname'] . ' ' . $profile['lastname']); ?></h1>
            <p class="subtitle"><?= htmlspecialchars($profile['position']); ?></p>
          </div>
          <div class="divider"></div>
          <div class="contact-info">
            <a href="mailto:<?= htmlspecialchars($profile['email']); ?>" target="_blank" class="contact-item">
              <i class="fas fa-envelope"></i><?= htmlspecialchars($profile['email']); ?>
            </a>
            <a href="<?= htmlspecialchars($profile['facebookUrl']); ?>" target="_blank" class="contact-item">
              <i class="fab fa-facebook"></i><?= htmlspecialchars($profile['facebook']); ?>
            </a>
            <a href="#" class="share-btn" target="_blank"><i class="fas fa-share"></i>Share Profile</a>
          </div>
        </div>
      </div>
    </section>

    <main>
      <!-- Intro -->
      <section>
        <h2>Intro</h2>
        <div class="intro-content">
          <p><?= htmlspecialchars($profile['introContent']); ?></p>
        </div>
      </section>

      <!-- Skills -->
      <section id="skills">
        <h2>Skills</h2>
        <div class="skills-grid">
          <?php foreach ($profileSkills as $skill): ?>
            <?php if (!empty($skill['skillsName'])): ?>
              <div class="skill-item">
                <div class="skill-icon">
                  <?php if (strpos($skill['skillsUrl'], 'fa-') !== false): ?>
                    <!-- ใช้ Font Awesome -->
                    <i class="<?= htmlspecialchars($skill['skillsUrl']); ?>"></i>
                  <?php else: ?>
                    <!-- ใช้รูปภาพ -->
                    <img src="<?= htmlspecialchars($skill['skillsUrl']); ?>" alt="<?= htmlspecialchars($skill['skillsName']); ?>" style="width:40px; height:40px;">
                  <?php endif; ?>
                </div>
                <div class="skill-name"><?= htmlspecialchars($skill['skillsName']); ?></div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Work Experience -->
      <section class="experience" id="experience">
        <h2>Work Experience</h2>
        <?php foreach ($workExperience as $exp): ?>
          <div class="timeline-card">
            <div class="timeline-item">
              <div class="title">
                <span class="date">
                  <?php
                  $startDate = date("M d, Y", strtotime($exp['startDate']));
                  $endDate = $exp['IsCurrent'] == 1 ? "Current" : date("M d, Y", strtotime($exp['endDate']));
                  echo $startDate . " - " . $endDate;
                  ?>
                </span>
                <span class="employment"><?= htmlspecialchars($exp['employeeType']); ?></span>
              </div>

              <ul>
                <li><?= htmlspecialchars($exp['companyName']); ?></li>
                <li>Position: <?= htmlspecialchars($exp['position']); ?></li>
                <li><?= htmlspecialchars($exp['jobDescription']); ?></li>

                <?php if ($exp['remarks'] !== null): ?>
                  <li><?= htmlspecialchars($exp['remarks']); ?></li>
                <?php endif; ?>

              </ul>
            </div>
          </div>
        <?php endforeach; ?>
      </section>

      <!-- Education -->
      <section class="education" id="education">
        <h2>Education</h2>
        <?php foreach ($education as $edu): ?>
          <div class="timeline-card">
            <div class="timeline-item">
              <div class="title">
                <?php
                $startEdu = date("Y", strtotime($edu['startDate']));
                $endEdu = $edu['IsCurrent'] == 1 ? "Current" : date("Y", strtotime($edu['endDate']));
                echo $startEdu . " - " . $endEdu . " " . htmlspecialchars($edu['degree']);
                ?>
              </div>
              <ul>
                <li><?= htmlspecialchars($edu['educationName']); ?></li>
                <li><?= htmlspecialchars($edu['facultyName']); ?></li>
                <li>Major: <?= htmlspecialchars($edu['majorName']); ?></li>
              </ul>
            </div>
          </div>
        <?php endforeach; ?>
      </section>
    </main>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 Pronvootikul, S. All rights reserved.</p>
    </div>
  </footer>

  <script src="scripts/index.js"></script>
</body>

</html>