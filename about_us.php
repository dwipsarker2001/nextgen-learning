<?php
session_start();
include 'includes/helpers.php';
$page_title = "About Us | Nextgen Learning";
ob_start();
?>


<main>
	<!-- --------------------------------------------
				Page Introduction
	------------------------------------------ -->
	<section class="py-4">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="bg-light p-4 text-center rounded-3">
						<h2 class="m-0">About NextGen Learning</h2>
						<!-- Breadcrumb -->
						<div class="d-flex justify-content-center">
							<nav aria-label="breadcrumb">
								<ol class="breadcrumb breadcrumb-dots mb-0">
								<li class="breadcrumb-item"><a href="index.php">Home</a></li>
								<li class="breadcrumb-item active" aria-current="page">
									About Us
								</li>
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	
	<!-- --------------------------------------------
				Page Introduction
	------------------------------------------ -->
	<section class="pt-0 pt-md-5">
		<div class="container">

			<div class="row align-items-top">
				<div class="col-lg-8 mt-4 mt-lg-0 mx-auto">
					<!-- Title -->
					<h3 class="mb-2">NextGen Learning</h3>
					<strong class="d-block mb-3">Empowering the Next Generation of Learners</strong>

					<!-- Content -->
					<p>NextGen Learning is a forward-thinking, inclusive e-learning platform dedicated to breaking down the barriers to quality education. Based at the Shahjalal University of Science and Technology, we recognize that while technology is transforming how we learn, many students in Bangladesh still face challenges like high costs and a lack of localized, interactive content.</p>

					<p><strong>Our mission</strong> is to bridge this gap by providing a digital environment that is both globally competitive and locally relevant.</p>

					<h5 class="mt-4">Why Choose NextGen Learning?</h5>
					<p>We offer a balanced learning experience that combines the diversity of global platforms with the affordability and community focus required for local success.</p>

					<ul class="list-group list-group-borderless mt-4">
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Diverse Course Catalog:</strong> Access a wide range of subjects, from STEM (Science, Technology, Engineering, and Mathematics) to Arts, Humanities, Business, and Design.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Interactive Learning:</strong> Move beyond passive video watching with live classes, real-time Q&A forums, and active community engagement.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Affordability & Accessibility:</strong> We believe education should not be restricted by socioeconomic status. Our courses are designed to be cost-effective, featuring secure local payment gateways like bKash and Nagad.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Expert Instruction:</strong> Learn from skilled educators who are passionate about sharing their expertise and connecting with students.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Flexible Progression:</strong> Study at your own pace with detailed progress tracking and a user-friendly dashboard designed for your schedule.
							</span>
						</li>
					</ul>

					<h5 class="mt-4">Our Vision for the Future</h5>
					<p>Education is a journey of continuous improvement. We are committed to evolving our platform with innovative features, including:</p>

					<ul class="list-group list-group-borderless mt-4">
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">AI-Powered Personalization:</strong> Course recommendations tailored to your unique learning style.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Gamification:</strong> Engaging techniques to boost motivation and reward your progress.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Official Recognition:</strong> Digital certificates to help you advance in your professional career.
							</span>
						</li>
						<li class="list-group-item d-flex align-items-start">
							<i class="bi bi-patch-check-fill text-success me-2 mt-1"></i>
							<span>
								<strong class="text-nowrap">Mobile Learning:</strong> A dedicated app to ensure you can learn anytime, anywhere.
							</span>
						</li>
					</ul>

					<p class="mt-4">Join NextGen Learning today and take your skills to new heights!</p>
				</div>
			</div>
		</div>
	</section>
	<!-- =======================
About founder END -->


	<!-- =======================
Award and Team START -->
	<section class="bg-light">
		<div class="container">
			<div class="row">
				<!-- Our team START -->
				<div class="col-md-12">
					<!-- Title and button -->
					<div class="d-sm-flex justify-content-sm-between">
						<h2 class="mb-0">Meet Our Instructors</h2>
						<a href="coming_soon.php" class="btn btn-light mt-2">Join Team</a>
					</div>

					<!-- Slider START -->
					<div class="tiny-slider arrow-round arrow-creative arrow-blur arrow-hover mt-2 mt-sm-5">
						<div class="tiny-slider-inner" data-autoplay="true" data-arrow="true" data-dots="false" data-items="5" data-items-lg="3" data-items-md="2">

							<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/user-01.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Dwip Sarkar</a></h6>
								<p class="mb-0 small">Developer</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
								</ul>
							</div>

							<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/user-09.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Anik Dey</a></h6>
								<p class="mb-0 small">Developer</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star-half-alt text-warning"></i></li>
								</ul>
							</div>

							<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/user-04.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Avi Das</a></h6>
								<p class="mb-0 small">Graphic Designer</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star-half-alt text-warning"></i></li>
								</ul>
							</div>

							<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/sufian.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Sufian Ahmed</a></h6>
								<p class="mb-0 small">IT Expert</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star-half-alt text-warning"></i></li>
								</ul>
							</div>

							<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/user-04.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Avi Das</a></h6>
								<p class="mb-0 small">Graphic Designer</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star-half-alt text-warning"></i></li>
								</ul>
							</div>

														<!-- Avatar item -->
							<div class="text-center">
								<!-- Avatar -->
								<div class="avatar avatar-xxl mb-3">
									<img class="avatar-img rounded-circle" src="uploads/img/users/user-01.jpg" alt="avatar">
								</div>
								<!-- Info -->
								<h6 class="mb-0"><a href="#">Dwip Sarkar</a></h6>
								<p class="mb-0 small">Developer</p>
								<!-- Rating -->
								<ul class="list-inline mb-0">
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
									<li class="list-inline-item me-0 small"><i class="fas fa-star text-warning"></i></li>
								</ul>
							</div>
						</div>
					</div>
					<!-- Slider END -->
				</div>
				<!-- Our team END -->
			</div>
		</div>
	</section>
	<!-- =======================
Award and Team END -->

</main>
<!-- **************** MAIN CONTENT END **************** -->


<?php
$content = ob_get_clean();
include('layouts/website.php');
?>