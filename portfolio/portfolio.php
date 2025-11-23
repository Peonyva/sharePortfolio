<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

$userID = intval($_GET['user']);

try {

  // Query 1: User
  $stmt = $conn->prepare("SELECT userID, firstname, lastname, birthdate, email
        FROM user WHERE userID = :userID");
  $stmt->execute(['userID' => $userID]);
  $userData = $stmt->fetch(PDO::FETCH_ASSOC);

  // Query 2: Profile
  $stmt = $conn->prepare("SELECT professionalTitle, phone, facebook, facebookUrl,
            logoImage, profileImage, coverImage, introContent, skillsContent
        FROM profile WHERE userID = :userID");
  $stmt->execute(['userID' => $userID]);
  $profileData = $stmt->fetch(PDO::FETCH_ASSOC);


  // Merge user + profile
  $data = array_merge($userData, $profileData ?: []);

  // Full path images
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

  $project = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // full path ให้รูป project
  foreach ($project as &$projectItem) {
    if (!empty($projectItem['projectImage'])) {
      $projectItem['projectImage'] = "/uploads/{$userID}/" . $projectItem['projectImage'];
    }
  }
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
  <link rel="stylesheet" href="/styles/portfolio.css" />

</head>

<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <nav class="nav-container">
        <div class="logo">
          <img src="<?=htmlspecialchars($data['logoImage']);?>" alt="Logo" />My Portfolio
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
  <!-- End Header -->

  <div class="container">
    <section class="hero" id="aboutme">
      <div class="cover-photo">
        <img src="<?=htmlspecialchars($data['coverImage']);?>" alt="cover photo">
      </div>

      <!-- Profile -->
      <div class="profile">
        <div class="profile-content">
          <div class="profile-image">
            <img src="<?=htmlspecialchars($data['profileImage']);?>" alt="Profile Image" />
          </div>
          <div class="profile-info">
            <h1>John Doe</h1>
            <p class="subtitle">Full-Stack Developer</p>
          </div>
          <div class="divider"></div>
          <div class="contact-info">
            <a href="mailto:<?=htmlspecialchars($data['email']);?>" target="_blank" class="contact-item">
              <i class="fas fa-envelope"></i><?= htmlspecialchars($data['email']); ?>
            </a>
            <a href="<?=htmlspecialchars($data['facebookUrl']);?>" target="_blank" class="contact-item">
              <i class="fab fa-facebook"></i><?=htmlspecialchars($data['facebook']);?>
            </a>
            <a href="#" class="share-btn" target="_blank"><i class="fas fa-share"></i>Share Profile</a>
          </div>
        </div>
      </div>
    </section>
    <!-- End Section -->

    <main>
      <!-- Intro -->
      <section>
        <h2>Intro</h2>
        <div class="intro-content">
          <p><?=htmlspecialchars($data['introContent']);?></p>
        </div>
      </section>

      <!-- Skills  -->
      <section id="skills">
        <h2>Skills</h2>
        <!-- <ul> -->
         <p><?=htmlspecialchars($data['skillsContent']);?></p>
        <!-- </ul> -->
        <div class="skills-grid">
          <!-- php -->
          <div class="skill-item">
            <div class="skill-icon">
              <i class="fab fa-php skill-icon icon-php"></i>
            </div>
            <div class="skill-name">PHP</div>
          </div>
          <!-- javascript -->
          <div class="skill-item">
            <div class="skill-icon">
              <i class="fab fa-js-square skill-icon icon-javascript"></i>
            </div>
            <div class="skill-name">JavaScript</div>
          </div>
          <!-- html -->
          <div class="skill-item">
            <div class="skill-icon">
              <i class="fab fa-html5 skill-icon icon-html"></i>
            </div>
            <div class="skill-name">HTML</div>
          </div>
          <!-- css -->
          <div class="skill-item">
            <div class="skill-icon">
              <i class="fab fa-css3-alt skill-icon icon-css"></i>
            </div>
            <div class="skill-name">CSS</div>
          </div>
          <!-- mysql -->
          <div class="skill-item">
            <div class="skill-icon">
              <i class="fas fa-database skill-icon icon-mysql"></i>
            </div>
            <div class="skill-name">MySQL</div>
          </div>
          <!-- figma -->
          <div class="skill-item">
            <div class="skill-icon">
              <img src="image/figma.png" alt="Figma" style="width: 40px; height: 40px;">
            </div>
            <div class="skill-name">Figma</div>
          </div>
        </div>
      </section>

      <!-- Projects  -->
      <section class="projects" id="projects">
        <h2>Projects</h2>
        <!-- no.1 -->
        <div class="project-card">
          <img src="image/african-american-distribution-warehouse-worker-arm-scanning-box-code.jpg" alt="Project 1" />
          <div class="project-content">
            <h3>Inventory Management System</h3>
            <ul>
              <li>Developed a responsive inventory system for small businesses.</li>
              <li>Backend built with PHP and MySQL.</li>
              <li>Frontend using HTML, CSS, and JavaScript.</li>
            </ul>
            <div class="tech-grid">
              <span class="tech-item"><i class="fab fa-php icon-php"></i></span>
              <span class="tech-item"><i class="fab fa-html5 icon-html"></i></span>
              <span class="tech-item"><i class="fab fa-js-square icon-javascript"></i></span>
              <span class="tech-item"><i class="fas fa-database icon-mysql"></i></span>
            </div>
          </div>
        </div>
        <!-- no.2 -->
        <div class="project-card">
          <img src="image/6599112.jpg" alt="Project 2" />
          <div class="project-content">
            <h3>Portfolio Template Generator</h3>
            <ul>
              <li>Created a web-based tool for generating free portfolio templates.</li>
              <li>Integrated free hosting for user portfolios.</li>
            </ul>
            <div class="tech-grid">
              <span class="tech-item"><i class="fab fa-php icon-php"></i></span>
              <span class="tech-item"><i class="fab fa-html5 icon-html"></i></span>
              <span class="tech-item"><i class="fab fa-css3-alt icon-css"></i></span>
            </div>
          </div>
        </div>
      </section>

      <!-- Work Experience -->
      <section class="experience" id="experience">
        <h2>Work Experience</h2>

        <div class="timeline-card">
          <div class="timeline-item">
            <div class="title">2024 — Full-Time Developer</div>
            <ul>
              <li>Company: Tech Solutions Co., Ltd.</li>
              <li>Position: Full-Stack Developer</li>
              <li>Developed ERP systems and APIs for internal use.</li>
            </ul>
          </div>
        </div>

        <div class="timeline-card">
          <div class="timeline-item">
            <div class="title">2022 — Internship (6 months)</div>
            <ul>
              <li>Company: ABC Software</li>
              <li>Position: Web Application Intern</li>
            </ul>
          </div>
        </div>
      </section>

      <!-- Education -->
      <section class="education" id="education">
        <h2>Education</h2>

        <div class="timeline-card">
          <div class="timeline-item">
            <div class="title">2020 – 2024 Bachelor's Degree</div>
            <ul>
              <li>University of Technology</li>
              <li>Faculty of Information Technology</li>
              <li>Major: Computer Science</li>
            </ul>
          </div>
        </div>

        <div class="timeline-card">
          <div class="timeline-item">
            <div class="title">2018 – 2020 High School</div>
            <ul>
              <li>XYZ High School</li>
              <li>Science and Mathematics Program</li>
            </ul>
          </div>
        </div>
      </section>


    </main>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 John Doe. All rights reserved.</p>
    </div>
  </footer>


  <script src="/portfolio/portfolio.js"></script>
</body>

</html>