<?php
/*---------------------------------------------
    Include essential files
---------------------------------------------*/
include('../includes/db.php');
include('../includes/session.php');
include('../includes/helpers.php');
include('../includes/get_courses.php');
include('../includes/get_course_topics.php');
include('../includes/get_course_lectures.php');

/*---------------------------------------------
    Get course data
---------------------------------------------*/
$course_id = $_GET['course_id'];
$course_lectures = get_course_lectures($conn, $course_id);

/*---------------------------------------------
    Page configuration
---------------------------------------------*/
$page_title = "Learning Room | Nextgen Learning";
ob_start();
?>

<style>
    :root {
        /* Changed from Indigo to Crimson/Red */
        --primary: #e11d48; 
        --primary-light: #fff1f2;
        --bg-body: #f8fafc;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
    }

    body { 
        background-color: var(--bg-body); 
        color: var(--text-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }
    
    /*---------------------------------------------
        Video Player Wrapper
    ---------------------------------------------*/
    .player-wrapper {
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .player-wrapper iframe { 
        aspect-ratio: 16/9; 
        width: 100%; 
        display: block; 
    }

    .video-title-section {
        background: #fff;
        padding: 24px;
        border-radius: 12px;
        margin-top: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .video-title-section h4 {
        color: var(--text-main);
        margin: 0;
        font-weight: 700;
        letter-spacing: -0.025em;
    }

    /*---------------------------------------------
        Curriculum Card Styling
    ---------------------------------------------*/
    .curriculum-card {
        height: 75vh;
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .curriculum-card .card-header {
        background: #fff;
        border-bottom: 1px solid var(--border-color);
        padding: 20px 24px;
    }

    .curriculum-card .card-header h3 {
        color: var(--text-main);
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .curriculum-body {
        flex-grow: 1;
        overflow-y: auto;
        padding: 0 !important;
    }
    
    /*---------------------------------------------
        Lecture Section Styling
    ---------------------------------------------*/
    .lecture-section-title {
        background: #f1f5f9;
        padding: 12px 24px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .lecture-section-title .badge {
        background: #fff;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        font-size: 0.7rem;
    }
    
    /*---------------------------------------------
        Lecture Item Styling
    ---------------------------------------------*/
    .lecture-item {
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .lecture-item:hover {
        background-color: #fff1f2; /* Light red hover */
    }
    
    .lecture-item.active {
        background-color: var(--primary-light); /* Soft red background */
        border-left: 4px solid var(--primary);
    }

    .lecture-item .play-icon-box {
        width: 32px;
        height: 32px;
        background: #f1f5f9;
        color: var(--text-muted);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .lecture-item.active .play-icon-box {
        background: var(--primary);
        color: #fff;
    }

    .lecture-item h6 {
        font-weight: 500;
        font-size: 0.95rem;
        margin-bottom: 2px;
        color: var(--text-main);
    }

    .lecture-item.active h6 {
        color: var(--primary);
        font-weight: 600;
    }

    .lecture-item p {
        color: var(--text-muted);
        font-size: 0.8rem;
        margin: 0;
    }
    
    /*---------------------------------------------
        Custom Scrollbar
    ---------------------------------------------*/
    .curriculum-body::-webkit-scrollbar { width: 5px; }
    .curriculum-body::-webkit-scrollbar-thumb { 
        background: #fda4af; /* Soft red scrollbar */
        border-radius: 10px; 
    }

    /*---------------------------------------------
        Responsive
    ---------------------------------------------*/
    @media (max-width: 991px) {
        .curriculum-card { height: 500px; margin-top: 2rem; }
    }
</style>

<main>
    <div class="container py-5">
        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="player-wrapper">
                    <iframe id="videoPlayer" src="" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                </div>
                
                <div class="video-title-section">
                    <p class="text-uppercase small fw-bold text-primary mb-1">Now Playing</p>
                    <h4 id="currentVideoTitle">Select a lesson to start learning</h4>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="curriculum-card">
                    <div class="card-header bg-opacity-10">
                        <h3 class="mb-0">Course Content</h3>
                    </div>
                    
                    <div class="curriculum-body">
                        <?php if (!empty($course_lectures)): ?>
                            <?php foreach ($course_lectures as $lecture): ?>
                                
                                <div class="lecture-section-title">
                                    <?php echo htmlspecialchars($lecture['title']); ?>
                                    <?php 
                                        $topics = get_course_topics($conn, $lecture['id']);
                                        $count = count($topics ?? []);
                                    ?>
                                    <span class="badge rounded-pill"><?php echo $count; ?> Lessons</span>
                                </div>

                                <?php if ($count > 0): ?>
                                    <?php foreach ($topics as $lesson): 
                                        $videoUrl = $lesson['video'];
                                        $videoId = '';
                                        if (preg_match('/[?&]v=([^&]+)/', $videoUrl, $matches)) { $videoId = $matches[1]; }
                                        elseif (preg_match('/youtu\.be\/([^?]+)/', $videoUrl, $matches)) { $videoId = $matches[1]; }
                                    ?>
                                        <div class="lecture-item play-video" 
                                             data-video-id="<?php echo htmlspecialchars($videoId); ?>" 
                                             data-title="<?php echo htmlspecialchars($lesson['title']); ?>">
                                            
                                            <div class="play-icon-box">
                                                <i class="fas fa-play"></i>
                                            </div>
                                            
                                            <div class="flex-grow-1">
                                                <h6><?php echo htmlspecialchars($lesson['title']); ?></h6>
                                                <p><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($lesson['duration']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <p class="text-muted">No content available.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const videoPlayer = document.getElementById('videoPlayer');
    const titleDisplay = document.getElementById('currentVideoTitle');
    const playItems = document.querySelectorAll('.play-video');
    
    function loadVideo(element) {
        const videoId = element.getAttribute('data-video-id');
        const title = element.getAttribute('data-title');
        
        if (videoId) {
            videoPlayer.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
            titleDisplay.innerText = title;
            
            playItems.forEach(item => item.classList.remove('active'));
            element.classList.add('active');
            
            if (window.innerWidth < 992) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }

    if (playItems.length > 0) loadVideo(playItems[0]);

    playItems.forEach(item => {
        item.addEventListener('click', function() { loadVideo(this); });
    });
});
</script>

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>