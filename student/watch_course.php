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
include('../includes/get_watched.php');

/*---------------------------------------------
    Get course data
---------------------------------------------*/
$course_id = $_GET['course_id'];
$user_id = $_SESSION['user_id'] ?? 0;
$course_lectures = get_course_lectures($conn, $course_id);

/*---------------------------------------------
    Page configuration
---------------------------------------------*/
$page_title = "Learning Room | Nextgen Learning";

$watched_topic_ids = [];
if ($user_id) {
    $stmt_w = $conn->prepare("SELECT DISTINCT topic_id FROM watched_topics WHERE user_id = ? AND course_id = ?");
    $stmt_w->bind_param("ii", $user_id, $course_id);
    $stmt_w->execute();
    $result_w = $stmt_w->get_result();
    while ($row_w = $result_w->fetch_assoc()) {
        $watched_topic_ids[] = (int)$row_w['topic_id'];
    }
    $stmt_w->close();
}
$watched_json = json_encode($watched_topic_ids);

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
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: 12px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .player-wrapper > div,
    .player-wrapper iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
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
                    <div id="videoPlayer"></div>
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
                                             data-title="<?php echo htmlspecialchars($lesson['title']); ?>"
                                             data-topic-id="<?php echo (int)$lesson['id']; ?>"
                                             data-watched="<?php echo in_array((int)$lesson['id'], $watched_topic_ids) ? '1' : '0'; ?>">
                                            
                                            <div class="play-icon-box">
                                                <i class="fas fa-play"></i>
                                            </div>
                                            
                                            <div class="flex-grow-1">
                                                <h6><?php echo htmlspecialchars($lesson['title']); ?></h6>
                                                <p><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($lesson['duration']); ?></p>
                                            </div>

                                            <?php if (in_array((int)$lesson['id'], $watched_topic_ids)): ?>
                                                <i class="fas fa-check-circle text-success ms-auto" style="font-size:1.1rem;"></i>
                                            <?php endif; ?>
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
let player;
let currentTopicId = null;
const courseId = <?= (int)$course_id ?>;
const watchedTopics = <?= $watched_json ?>;

function onYouTubeIframeAPIReady() {
    player = new YT.Player('videoPlayer', {
        height: '100%',
        width: '100%',
        videoId: '',
        playerVars: { rel: 0, autoplay: 1 },
        events: { 'onStateChange': onPlayerStateChange }
    });
}

function markWatched(topicId) {
    if (!topicId || watchedTopics.includes(topicId)) return;
    watchedTopics.push(topicId);

    const item = document.querySelector(`.play-video[data-topic-id="${topicId}"]`);
    if (item) {
        item.setAttribute('data-watched', '1');
        if (!item.querySelector('.fa-check-circle')) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-check-circle text-success ms-auto';
            icon.style.fontSize = '1.1rem';
            item.appendChild(icon);
        }
    }
}

function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.ENDED && currentTopicId) {
        logWatch(currentTopicId, courseId);
        markWatched(currentTopicId);
    }
}

function logWatch(topicId, courseId) {
    fetch('../includes/log_watch.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'topic_id=' + topicId + '&course_id=' + courseId
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const titleDisplay = document.getElementById('currentVideoTitle');
    const playItems = document.querySelectorAll('.play-video');

    function loadVideo(element) {
        const videoId = element.getAttribute('data-video-id');
        const title = element.getAttribute('data-title');
        const topicId = element.getAttribute('data-topic-id');

        if (videoId && player && player.loadVideoById) {
            player.loadVideoById(videoId);
            titleDisplay.innerText = title;
            currentTopicId = topicId ? parseInt(topicId) : null;

            playItems.forEach(item => item.classList.remove('active'));
            element.classList.add('active');

            if (window.innerWidth < 992) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    }

    if (playItems.length > 0) {
        setTimeout(() => loadVideo(playItems[0]), 500);
    }

    playItems.forEach(item => {
        item.addEventListener('click', function() { loadVideo(this); });
    });
});
</script>
<script src="https://www.youtube.com/iframe_api"></script>

<link rel="stylesheet" href="../chatbot/assets/home-widget.css">

<div class="ng-chat-widget" id="ngChatWidget">
    <button class="ng-chat-toggle" id="ngChatToggle" type="button" aria-label="Ask about this course">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <section class="ng-chat-window" id="ngChatWindow" aria-label="Course Chatbot" hidden>
        <header class="ng-chat-header">
            <div>
                <h2>Course Assistant</h2>
                <p>Ask about this course</p>
            </div>
            <button class="ng-chat-close" id="ngChatClose" type="button" aria-label="Close chatbot">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="ng-chat-messages" id="ngChatMessages" aria-live="polite">
            <div class="ng-chat-message bot">
                <div class="ng-chat-bubble">
                    Ask me anything about this course — topics, duration, pricing, or lessons.
                </div>
            </div>
        </div>

        <form class="ng-chat-form" id="ngChatForm" autocomplete="off">
            <label class="ng-chat-sr-only" for="ngChatInput">Ask about this course</label>
            <textarea id="ngChatInput" name="message" rows="2" maxlength="1000" placeholder="Ask about this course..." required></textarea>
            <button type="submit" aria-label="Send message">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </section>
</div>

<script>
window._ngCourseId = <?php echo (int) $course_id; ?>;
</script>
<script src="../chatbot/assets/course-widget.js"></script>

<?php
$content = ob_get_clean();
include('../layouts/student.php');
?>