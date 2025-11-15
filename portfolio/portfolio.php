<?php
$title = "My Portfolio";

if (!isset($_GET['user']) || !is_numeric($_GET['user'])) {
    header("Location: /login.php");
    exit;
}
$currentUserID = intval($_GET['user']);

require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// เริ่มต้นตัวแปร
$profile = null;
$profileSkills = [];
$workExperience = [];
$education = [];
$projects = [];
$isPublicFromDB = 0;
$isEverPublic = 0;

try {
    // ตรวจสอบว่าผู้ใช้มีอยู่จริง และดึงสถานะการเผยแพร่จากตาราง profile
    $stmt = $conn->prepare("SELECT p.userID, p.isPublic, p.isEverPublic 
                            FROM profile p 
                            WHERE p.userID = :userID");
    $stmt->execute(['userID' => $currentUserID]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        // ถ้าไม่มี profile ให้ไปหน้า editor
        header("Location: /portfolio/portfolio-editor.php?user=" . urlencode($currentUserID));
        exit;
    }

    $isPublicFromDB = intval($userData['isPublic'] ?? 0);
    $isEverPublic = intval($userData['isEverPublic'] ?? 0);

    // ถ้ายังไม่เคยเผยแพร่ (isEverPublic = 0) ให้ redirect ไป editor
    if ($isEverPublic === 0) {
        header("Location: /portfolio/portfolio-editor.php?user=" . urlencode($currentUserID));
        exit;
    }

    // ดึงข้อมูล Profile และ Skills
    $stmt = $conn->prepare("SELECT p.firstname, p.lastname, p.position, p.email, p.phone, p.facebook,
                 p.facebookUrl, p.logoImage, p.profileImage, p.coverImage, 
                 p.introContent, p.skillsContent, s.skillsName, s.skillsUrl
        FROM profile p
        LEFT JOIN profileSkill ps ON p.userID = ps.userID
        LEFT JOIN skills s ON ps.skillsID = s.skillsID
        WHERE p.userID = :userID");
    $stmt->execute(['userID' => $currentUserID]);
    $profileSkills = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $profile = !empty($profileSkills) ? $profileSkills[0] : null;

    // ดึงข้อมูล Work Experience
    $stmt = $conn->prepare("SELECT id, companyName, position, jobDescription, employeeType, startDate, endDate, IsCurrent, remarks
        FROM workexperience
        WHERE userID = :userID
        ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $currentUserID]);
    $workExperience = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูล Education
    $stmt = $conn->prepare("SELECT id, educationName, degree, facultyName, majorName, startDate, endDate, IsCurrent, remarks
        FROM education
        WHERE userID = :userID
        ORDER BY sortOrder ASC");
    $stmt->execute(['userID' => $currentUserID]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูล Projects
    $stmt = $conn->prepare("SELECT p.projectID, p.projectTitle, p.projectImage, p.keyPoint, s.skillsUrl
        FROM project p
        LEFT JOIN project_skill ps ON p.projectID = ps.projectID
        LEFT JOIN skills s ON ps.skillsID = s.skillsID
        WHERE p.userID = :userID");
    $stmt->execute(['userID' => $currentUserID]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/header-portfolio.php'; ?>

<!-- ส่วนควบคุม Portfolio -->
<div class="portfolio-controls">
    <!-- ปุ่มแก้ไข Portfolio -->
    <div class="edit-portfolio-button">
        <a href="/portfolio/portfolio-editor.php?user=<?php echo $currentUserID; ?>" 
           class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> Edit Portfolio
        </a>
    </div>

    <!-- Toggle สถานะ Public/Private -->
    <div class="portfolio-status-toggle">
        <label class="switch-container">
            <span class="status-label">Status: </span>
            <label class="switch">
                <input type="checkbox" id="portfolioPublishToggle" <?php echo ($isPublicFromDB == 1) ? 'checked' : ''; ?>>
                <span class="slider round"></span>
            </label>
            <span class="status-text"><?php echo ($isPublicFromDB == 1) ? 'Public' : 'Private'; ?></span>
        </label>
    </div>
</div>

<input type="hidden" id="portfolioUserID" value="<?php echo htmlspecialchars($currentUserID); ?>">

<div class="container">
    <main class="portfolio-content">
        
        <?php if ($profile): ?>
            <!-- Cover Image -->
            <?php if (!empty($profile['coverImage'])): ?>
                <section class="portfolio-cover">
                    <img src="<?php echo htmlspecialchars($profile['coverImage']); ?>" alt="Cover Image">
                </section>
            <?php endif; ?>

            <!-- Header Section -->
            <section class="portfolio-header">
                <?php if (!empty($profile['profileImage'])): ?>
                    <div class="profile-image">
                        <img src="<?php echo htmlspecialchars($profile['profileImage']); ?>" alt="Profile">
                    </div>
                <?php endif; ?>

                <div class="profile-info">
                    <h1 class="name"><?php echo htmlspecialchars($profile['firstname'] . ' ' . $profile['lastname']); ?></h1>
                    <?php if (!empty($profile['position'])): ?>
                        <h2 class="position"><?php echo htmlspecialchars($profile['position']); ?></h2>
                    <?php endif; ?>
                    
                    <div class="contact-info">
                        <?php if (!empty($profile['email'])): ?>
                            <span class="email"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($profile['email']); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['phone'])): ?>
                            <span class="phone"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($profile['phone']); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($profile['facebookUrl'])): ?>
                            <a href="<?php echo htmlspecialchars($profile['facebookUrl']); ?>" target="_blank" class="facebook">
                                <i class="fa-brands fa-facebook"></i> <?php echo htmlspecialchars($profile['facebook'] ?? 'Facebook'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- About Me Section -->
            <?php if (!empty($profile['introContent'])): ?>
                <section class="portfolio-section about-me">
                    <h2 class="section-title">About Me</h2>
                    <div class="content">
                        <?php 
                        $introLines = explode("\n", $profile['introContent']);
                        foreach ($introLines as $line) {
                            if (!empty(trim($line))) {
                                echo '<p>' . htmlspecialchars($line) . '</p>';
                            }
                        }
                        ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Skills Section -->
            <?php if (!empty($profile['skillsContent']) || !empty($profileSkills)): ?>
                <section class="portfolio-section skills">
                    <h2 class="section-title">Skills</h2>
                    
                    <?php if (!empty($profile['skillsContent'])): ?>
                        <div class="skills-description">
                            <?php 
                            $skillLines = explode("\n", $profile['skillsContent']);
                            foreach ($skillLines as $line) {
                                if (!empty(trim($line))) {
                                    echo '<p>' . htmlspecialchars($line) . '</p>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($profileSkills)): ?>
                        <div class="skills-list">
                            <?php 
                            $displayedSkills = [];
                            foreach ($profileSkills as $skill): 
                                if (!empty($skill['skillsName']) && !in_array($skill['skillsName'], $displayedSkills)):
                                    $displayedSkills[] = $skill['skillsName'];
                            ?>
                                <div class="skill-item">
                                    <?php if (!empty($skill['skillsUrl'])): ?>
                                        <img src="<?php echo htmlspecialchars($skill['skillsUrl']); ?>" 
                                             alt="<?php echo htmlspecialchars($skill['skillsName']); ?>">
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($skill['skillsName']); ?></span>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Work Experience Section -->
            <?php if (!empty($workExperience)): ?>
                <section class="portfolio-section work-experience">
                    <h2 class="section-title">Work Experience</h2>
                    <div class="timeline">
                        <?php foreach ($workExperience as $work): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <h3 class="position"><?php echo htmlspecialchars($work['position']); ?></h3>
                                    <h4 class="company"><?php echo htmlspecialchars($work['companyName']); ?></h4>
                                    <p class="employment-type"><?php echo htmlspecialchars($work['employeeType']); ?></p>
                                    <p class="date-range">
                                        <?php 
                                        echo date('M Y', strtotime($work['startDate']));
                                        echo ' - ';
                                        echo $work['IsCurrent'] ? 'Present' : date('M Y', strtotime($work['endDate']));
                                        ?>
                                    </p>
                                    <?php if (!empty($work['jobDescription'])): ?>
                                        <div class="description">
                                            <?php 
                                            $descLines = explode("\n", $work['jobDescription']);
                                            echo '<ul>';
                                            foreach ($descLines as $line) {
                                                if (!empty(trim($line))) {
                                                    echo '<li>' . htmlspecialchars($line) . '</li>';
                                                }
                                            }
                                            echo '</ul>';
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($work['remarks'])): ?>
                                        <p class="remarks"><?php echo htmlspecialchars($work['remarks']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Education Section -->
            <?php if (!empty($education)): ?>
                <section class="portfolio-section education">
                    <h2 class="section-title">Education</h2>
                    <div class="timeline">
                        <?php foreach ($education as $edu): ?>
                            <div class="timeline-item">
                                <div class="timeline-content">
                                    <h3 class="degree"><?php echo htmlspecialchars($edu['degree']); ?></h3>
                                    <h4 class="institution"><?php echo htmlspecialchars($edu['educationName']); ?></h4>
                                    <p class="field"><?php echo htmlspecialchars($edu['facultyName'] . ' - ' . $edu['majorName']); ?></p>
                                    <p class="date-range">
                                        <?php 
                                        echo date('M Y', strtotime($edu['startDate']));
                                        echo ' - ';
                                        echo $edu['IsCurrent'] ? 'Present' : date('M Y', strtotime($edu['endDate']));
                                        ?>
                                    </p>
                                    <?php if (!empty($edu['remarks'])): ?>
                                        <p class="remarks"><?php echo htmlspecialchars($edu['remarks']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Projects Section -->
            <?php if (!empty($projects)): ?>
                <section class="portfolio-section projects">
                    <h2 class="section-title">Projects</h2>
                    <div class="projects-grid">
                        <?php 
                        $groupedProjects = [];
                        foreach ($projects as $project) {
                            $projectID = $project['projectID'];
                            if (!isset($groupedProjects[$projectID])) {
                                $groupedProjects[$projectID] = [
                                    'projectTitle' => $project['projectTitle'],
                                    'projectImage' => $project['projectImage'],
                                    'keyPoint' => $project['keyPoint'],
                                    'skills' => []
                                ];
                            }
                            if (!empty($project['skillsUrl'])) {
                                $groupedProjects[$projectID]['skills'][] = $project['skillsUrl'];
                            }
                        }

                        foreach ($groupedProjects as $project): 
                        ?>
                            <div class="project-card">
                                <?php if (!empty($project['projectImage'])): ?>
                                    <div class="project-image">
                                        <img src="<?php echo htmlspecialchars($project['projectImage']); ?>" 
                                             alt="<?php echo htmlspecialchars($project['projectTitle']); ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="project-content">
                                    <h3 class="project-title"><?php echo htmlspecialchars($project['projectTitle']); ?></h3>
                                    
                                    <?php if (!empty($project['keyPoint'])): ?>
                                        <div class="project-description">
                                            <?php 
                                            $keyPoints = explode("\n", $project['keyPoint']);
                                            echo '<ul>';
                                            foreach ($keyPoints as $point) {
                                                if (!empty(trim($point))) {
                                                    echo '<li>' . htmlspecialchars($point) . '</li>';
                                                }
                                            }
                                            echo '</ul>';
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($project['skills'])): ?>
                                        <div class="project-skills">
                                            <?php foreach ($project['skills'] as $skillUrl): ?>
                                                <img src="<?php echo htmlspecialchars($skillUrl); ?>" alt="Skill">
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-profile-message">
                <p>No portfolio data available. Please <a href="/portfolio/portfolio-editor.php?user=<?php echo $currentUserID; ?>">edit your portfolio</a> to add information.</p>
            </div>
        <?php endif; ?>

    </main>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/layout/footer.php'; ?>

<script src="/portfolio/portfolio-toggle.js"></script>

</body>
</html>