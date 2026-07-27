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

$resume_topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;

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
    .quiz-btn {
        background: linear-gradient(135deg, #e11d48, #be123c);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.25s ease;
    }
    .quiz-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(225, 29, 72, 0.35);
        color: #fff;
    }
    .quiz-btn:disabled {
        opacity: 0.5;
        transform: none;
        box-shadow: none;
    }
    .quiz-option {
        display: block;
        width: 100%;
        padding: 12px 16px;
        margin-bottom: 8px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
        text-align: left;
    }
    .quiz-option:hover {
        border-color: #e11d48;
        background: #fff1f2;
    }
    .quiz-option.selected {
        border-color: #e11d48;
        background: #fff1f2;
        font-weight: 600;
    }
    .quiz-option.correct {
        border-color: #22c55e;
        background: #f0fdf4;
        color: #15803d;
    }
    .quiz-option.wrong {
        border-color: #ef4444;
        background: #fef2f2;
        color: #b91c1c;
    }
    .quiz-progress {
        height: 4px;
        border-radius: 2px;
        background: #e2e8f0;
    }
    .quiz-progress .fill {
        height: 100%;
        border-radius: 2px;
        background: linear-gradient(90deg, #e11d48, #f43f5e);
        transition: width 0.4s ease;
    }
    #quizModal .text-muted {
        color: #64748b !important;
    }
    #quizModal .modal-title,
    #quizModal h5 {
        color: #0f172a !important;
    }
    #quizResultTitle {
        color: #0f172a !important;
    }
    #quizResultMsg {
        color: #475569 !important;
        font-size: 0.95rem;
        font-weight: 500;
    }
    #quizCounter {
        color: #64748b !important;
        font-weight: 600;
    }
    #quizQuestion {
        color: #0f172a !important;
    }
    .quiz-option {
        color: #1e293b;
    }
    #quizExplanation {
        color: #334155 !important;
    }

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
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="flex-grow-1 min-width-0">
                            <p class="text-uppercase small fw-bold text-primary mb-1">Now Playing</p>
                            <h4 id="currentVideoTitle" class="text-truncate">Select a lesson to start learning</h4>
                        </div>
                        <button id="quizBtn" class="quiz-btn flex-shrink-0" onclick="openQuiz()" title="Test your knowledge on this topic">
                            <i class="fas fa-question-circle me-1"></i> Quiz
                        </button>
                    </div>
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

<!-- ================= QUIZ MODAL ================= -->
<div class="modal fade" id="quizModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-question-circle text-primary me-2"></i>
                    <span id="quizTopicLabel">Quiz</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">

                <div class="quiz-progress mb-4">
                    <div class="fill" id="quizProgress" style="width:0%;"></div>
                </div>

                <div id="quizQuestionArea">
                    <p class="small text-muted mb-1" id="quizCounter">Question 1 of 5</p>
                    <h5 class="fw-bold mb-3" id="quizQuestion">Loading...</h5>
                    <div id="quizOptions"></div>
                </div>

                <div id="quizResultArea" class="d-none text-center py-4">
                    <div id="quizScoreCircle" class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                         style="width:100px;height:100px;border-radius:50%;background:#f1f5f9;font-size:2rem;font-weight:700;">
                        <span id="quizScore">0/5</span>
                    </div>
                    <h5 class="fw-bold" id="quizResultTitle">Great Job!</h5>
                    <p class="text-muted mb-3" id="quizResultMsg">You answered 0 correctly.</p>
                    <button class="btn btn-outline-primary rounded-pill px-4 me-2" onclick="closeQuiz()">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button class="btn btn-dark rounded-pill px-4" onclick="startQuiz()">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </button>
                </div>

                <div id="quizLoader" class="d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Generating Quiz Questions...</h5>
                    <p class="text-muted small mb-0">Our AI is preparing custom questions based on this lesson content. Please wait a moment.</p>
                </div>

                <div id="quizErrorArea" class="d-none text-center py-4">
                    <div class="text-danger mb-3" style="font-size: 2.5rem;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Quiz Generation Failed</h5>
                    <p class="text-muted mb-4" id="quizErrorMessage">Unable to generate quiz questions at this moment. Please try again.</p>
                    <button class="btn btn-outline-secondary rounded-pill px-4 me-2" onclick="closeQuiz()">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button class="btn btn-primary rounded-pill px-4" onclick="startQuiz()">
                        <i class="fas fa-redo me-1"></i> Try Again
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let player;
let currentTopicId = null;
const courseId = <?= (int)$course_id ?>;
const resumeTopicId = <?= $resume_topic_id ?>;
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

let quizQuestions = [];
let quizIndex = 0;
let quizScore = 0;
let isQuizLoading = false;

function setQuizBtnLoading(loading) {
    const quizBtn = document.getElementById('quizBtn');
    if (!quizBtn) return;
    if (loading) {
        quizBtn.disabled = true;
        quizBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
    } else {
        quizBtn.disabled = false;
        quizBtn.innerHTML = '<i class="fas fa-question-circle me-1"></i> Quiz';
    }
}

function openQuiz() {
    if (isQuizLoading) return;

    if (quizQuestions.length === 0) {
        startQuiz();
    } else {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quizModal'));
        modal.show();
    }
}

function startQuiz() {
    if (isQuizLoading) return;
    if (!currentTopicId) {
        alert('Please select a lesson first.');
        return;
    }

    isQuizLoading = true;
    setQuizBtnLoading(true);

    const topicItem = document.querySelector(`.play-video[data-topic-id="${currentTopicId}"]`);
    const topicTitle = topicItem ? topicItem.getAttribute('data-title') : 'this topic';
    document.getElementById('quizTopicLabel').textContent = 'Quiz: ' + topicTitle;

    document.getElementById('quizQuestionArea').classList.add('d-none');
    document.getElementById('quizResultArea').classList.add('d-none');
    document.getElementById('quizErrorArea').classList.add('d-none');
    document.getElementById('quizLoader').classList.remove('d-none');
    document.getElementById('quizProgress').style.width = '0%';

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quizModal'));
    modal.show();

    fetch('../chatbot/quiz.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ topic_id: currentTopicId, course_id: courseId })
    })
    .then(r => r.json())
    .then(data => {
        isQuizLoading = false;
        setQuizBtnLoading(false);
        document.getElementById('quizLoader').classList.add('d-none');

        if (!data.success || !data.quiz || !data.quiz.questions || data.quiz.questions.length === 0) {
            const msg = (data && data.message) ? data.message : 'Failed to generate quiz questions. Please try again.';
            document.getElementById('quizErrorMessage').textContent = msg;
            document.getElementById('quizErrorArea').classList.remove('d-none');
            return;
        }

        quizQuestions = data.quiz.questions;
        quizIndex = 0;
        quizScore = 0;
        showQuestion();
    })
    .catch(() => {
        isQuizLoading = false;
        setQuizBtnLoading(false);
        document.getElementById('quizLoader').classList.add('d-none');
        document.getElementById('quizErrorMessage').textContent = 'Network error while connecting to server. Please try again.';
        document.getElementById('quizErrorArea').classList.remove('d-none');
    });
}

function showQuestion() {
    if (quizIndex >= quizQuestions.length) {
        showResult();
        return;
    }

    const q = quizQuestions[quizIndex];
    document.getElementById('quizQuestionArea').classList.remove('d-none');
    document.getElementById('quizResultArea').classList.add('d-none');
    document.getElementById('quizCounter').textContent = 'Question ' + (quizIndex + 1) + ' of ' + quizQuestions.length;
    document.getElementById('quizQuestion').textContent = q.question;
    document.getElementById('quizProgress').style.width = ((quizIndex / quizQuestions.length) * 100) + '%';

    const container = document.getElementById('quizOptions');
    container.innerHTML = '';

    const explanationDiv = document.createElement('div');
    explanationDiv.id = 'quizExplanation';
    explanationDiv.className = 'd-none small text-muted mt-2 p-2 rounded';
    container.appendChild(explanationDiv);

    q.options.forEach((opt, i) => {
        const btn = document.createElement('button');
        btn.className = 'quiz-option';
        btn.textContent = opt;
        btn.onclick = function() {
            if (container.querySelector('.correct') || container.querySelector('.wrong')) return;

            document.querySelectorAll('.quiz-option').forEach(b => b.disabled = true);
            this.classList.add('selected');

            const correctBtns = document.querySelectorAll('.quiz-option');
            correctBtns[q.correct].classList.add('correct');

            if (i === q.correct) {
                quizScore++;
            } else {
                this.classList.add('wrong');
                this.classList.remove('selected');
            }

            if (q.explanation) {
                explanationDiv.textContent = '💡 ' + q.explanation;
                explanationDiv.className = 'small mt-2 p-2 rounded bg-info bg-opacity-10 text-muted';
            }

            setTimeout(() => {
                quizIndex++;
                showQuestion();
            }, 2000);
        };
        container.appendChild(btn);
    });
}

function showResult() {
    document.getElementById('quizQuestionArea').classList.add('d-none');
    document.getElementById('quizResultArea').classList.remove('d-none');
    document.getElementById('quizProgress').style.width = '100%';
    document.getElementById('quizScore').textContent = quizScore + '/' + quizQuestions.length;

    const scoreCircle = document.getElementById('quizScoreCircle');
    const pct = quizScore / quizQuestions.length;

    if (pct >= 0.8) {
        scoreCircle.style.background = '#dcfce7';
        scoreCircle.style.color = '#15803d';
        document.getElementById('quizResultTitle').textContent = 'Excellent! 🌟';
        document.getElementById('quizResultMsg').textContent = 'You answered ' + quizScore + ' of ' + quizQuestions.length + ' correctly!';
    } else if (pct >= 0.5) {
        scoreCircle.style.background = '#fef9c3';
        scoreCircle.style.color = '#a16207';
        document.getElementById('quizResultTitle').textContent = 'Good Job! 👍';
        document.getElementById('quizResultMsg').textContent = 'You answered ' + quizScore + ' of ' + quizQuestions.length + ' correctly.';
    } else {
        scoreCircle.style.background = '#fee2e2';
        scoreCircle.style.color = '#b91c1c';
        document.getElementById('quizResultTitle').textContent = 'Keep Learning! 📖';
        document.getElementById('quizResultMsg').textContent = 'You answered ' + quizScore + ' of ' + quizQuestions.length + ' correctly. Review the topic and try again.';
    }
}

function closeQuiz() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('quizModal'));
    if (modal) modal.hide();
}

document.addEventListener('DOMContentLoaded', function() {
    const titleDisplay = document.getElementById('currentVideoTitle');
    const playItems = document.querySelectorAll('.play-video');

    function loadVideo(element) {
        const videoId = element.getAttribute('data-video-id');
        const title = element.getAttribute('data-title');
        const topicId = element.getAttribute('data-topic-id');

        currentTopicId = topicId ? parseInt(topicId) : null;
        quizQuestions = [];

        if (videoId && player && player.loadVideoById) {
            player.loadVideoById(videoId);
        }

        titleDisplay.innerText = title;

        playItems.forEach(item => item.classList.remove('active'));
        element.classList.add('active');

        if (window.innerWidth < 992) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function findTopicElement(topicId) {
        return document.querySelector(`.play-video[data-topic-id="${topicId}"]`);
    }

    const initialTarget = resumeTopicId ? findTopicElement(resumeTopicId) : null;
    if (initialTarget) {
        setTimeout(() => loadVideo(initialTarget), 500);
        initialTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else if (playItems.length > 0) {
        setTimeout(() => loadVideo(playItems[0]), 500);
    }

    playItems.forEach(item => {
        item.addEventListener('click', function() { loadVideo(this); });
    });
});
</script>
<script src="https://www.youtube.com/iframe_api"></script>

<link rel="stylesheet" href="../chatbot/assets/home-widget.css?v=<?= file_exists(__DIR__ . '/../chatbot/assets/home-widget.css') ? filemtime(__DIR__ . '/../chatbot/assets/home-widget.css') : time() ?>">

<div class="ng-chat-widget" id="ngChatWidget" data-base="../">
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
                <div class="ng-chat-avatar"><i class="bi bi-robot"></i></div>
                <div class="ng-chat-bubble">Ask me anything about this course — topics, duration, pricing, or lessons.</div>
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
<script src="../chatbot/assets/course-widget.js?v=<?= file_exists(__DIR__ . '/../chatbot/assets/course-widget.js') ? filemtime(__DIR__ . '/../chatbot/assets/course-widget.js') : time() ?>"></script>

<?php
$hide_default_chatbot = true;
$content = ob_get_clean();
include('../layouts/student.php');
?>