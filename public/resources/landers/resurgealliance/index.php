<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Resurge Alliance</title>

	<!-- Bootstrap CSS -->
	<link
			href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
			rel="stylesheet"
	/>

	<!-- Bootstrap Icons -->
	<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
	/>

	<style>
        :root {
            --primary-blue: #1690f2;
            --primary-blue-dark: #0c7ed9;
            --text-dark: #1d2433;
            --text-muted: #5e6675;
            --light-bg: #f6f9fd;
            --card-border: #e9eef5;
            --footer-bg-1: #0b1730;
            --footer-bg-2: #142548;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text-dark);
            background: #ffffff;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            padding-top: 1rem;
            padding-bottom: 1rem;
            background: #EFEFEF;
        }

        .navbar-brand img {
            max-height: 62px;
            width: auto;
        }

        .nav-link {
            color: #2b3240;
            font-weight: 500;
            margin: 0 0.35rem;
        }

        .nav-link.active,
        .nav-link:hover {
            color: var(--primary-blue);
        }

        .btn-primary-small {
            background: #323645;
            padding: 0.4rem 1rem;
            color: #fff;
        }

        .btn-primary-small:hover {
            background: linear-gradient(180deg, #68696C 0%, #323645 100%);
            color: #fff;
            transform: translateY(-1px);
        }

        .btn-primary-custom {
            background: linear-gradient(180deg, #1da1ff 0%, #0f86ea 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 6px 14px rgba(22, 144, 242, 0.25);
            transition: 0.25s ease;
        }

        .btn-primary-custom:hover {
            background: linear-gradient(180deg, #1494ef 0%, #0a78d4 100%);
            color: #fff;
            transform: translateY(-1px);
        }

        .hero-section {
            position: relative;
            overflow: hidden;
            background: url(/resources/landers/ctpupgrade/images/hero-bg.png);
            background-size: cover;
            background-position: center;
            padding: 10rem 0;
        }

        .hero-content h1 {
            font-size: clamp(2rem, 4vw, 3.4rem);
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 1.2rem;
            max-width: 620px;
        }

        .hero-content p {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 540px;
            margin-bottom: 2rem;
        }

        .hero-graphic {
            text-align: center;
        }

        .hero-graphic img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
        }

        .services-section {
            padding: 4rem 0 2.5rem;
            background: #fff;
        }

        .service-item {
            text-align: center;
            padding: 1rem 1rem 0;
            max-width: 320px;
            margin: 0 auto 2rem;
        }

        .icon-box {
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #1da1ff 0%, #0f86ea 100%);
            color: #fff;
            font-size: 1.9rem;
            box-shadow: 0 8px 18px rgba(22, 144, 242, 0.2);
        }

        .service-item h4 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
        }

        .service-item p {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.65;
            margin: 0;
        }

        .results-section {
            padding: 1.5rem 0 5rem;
            background: #fff;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 3vw, 2.8rem);
            font-weight: 800;
            margin-bottom: 2.5rem;
        }

        .case-card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(18, 38, 63, 0.08);
            height: 100%;
            transition: 0.25s ease;
        }

        .case-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(18, 38, 63, 0.12);
        }

        .case-card .card-img-wrap {
            border: 6px solid var(--primary-blue);
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
        }

        .case-card img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            display: block;
        }

        .case-card .card-body {
            padding: 1.2rem 1rem 1.4rem;
        }

        .case-card h5 {
            font-size: 1.5rem;
            font-weight: 500;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .case-card h5 span {
            font-weight: 700;
        }

        .read-more-link {
            color: var(--primary-blue);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .read-more-link:hover {
            color: var(--primary-blue-dark);
        }

        .footer-top-bar {
            height: 10px;
            background: var(--primary-blue);
        }

        .site-footer {
            background:
                    linear-gradient(135deg, rgba(8, 20, 42, 0.98), rgba(19, 38, 75, 0.98)),
                    url("https://placehold.co/1600x500/102240/1d3768?text=Footer+Background");
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 3rem 0 1.5rem;
        }

        .footer-logo img {
            max-width: 220px;
            height: auto;
            margin: 0 auto;
        }

        .footer-contact-item i {
            color: var(--primary-blue);
            font-size: 1.1rem;
        }

        .footer-contact-item a {
            color: #fff;
        }

        .footer-bottom {
            text-align: center;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.95rem;
        }

        @media (max-width: 991.98px) {
            .hero-section {
                padding: 4rem 0 3rem;
            }

            .hero-content {
                text-align: center;
                margin-bottom: 2rem;
            }

            .hero-content h1,
            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }

            .navbar .btn-primary-custom {
                margin-top: 0.8rem;
                display: inline-block;
            }

        }

        @media (max-width: 767.98px) {
            .service-item h4 {
                font-size: 1.35rem;
            }

            .case-card img {
                height: 220px;
            }

            .hero-section {
                background-position: left;
            }
        }
	</style>
</head>
<body>

<!-- Header / Navbar -->
<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="#">
			<img
					src="/resources/landers/ctpupgrade/images/logo.png"
					alt="Resurge Alliance Logo"
			/>
		</a>

		<button
				class="navbar-toggler"
				type="button"
				data-bs-toggle="collapse"
				data-bs-target="#mainNav"
				aria-controls="mainNav"
				aria-expanded="false"
				aria-label="Toggle navigation"
		>
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse justify-content-lg-center" id="mainNav">
			<ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center">
				<li class="nav-item"><a class="nav-link" href="#">Home</a></li>
				<li class="nav-item"><a class="nav-link active" href="#services">Services</a></li>
			</ul>
		</div>

		<div class="d-none d-lg-block">
			<a href="https://resurgealliance.com/login" class="btn btn-primary-custom">Login</a>
		</div>
	</div>
</nav>

<!-- Hero -->
<section class="hero-section">
	<div class="container">
		<div class="row align-items-center g-5">
			<div class="col-lg-6">
				<div class="hero-content">
					<h1>Ignite Your Growth with Data-Driven Marketing</h1>
					<p>
						Strategic campaigns, SEO, and social media solutions to surge your ROI.
					</p>
					<a href="https://resurgealliance.com/login" class="btn btn-primary-custom btn-lg">Start Your Surge</a>
				</div>
			</div>


		</div>
	</div>
</section>

<!-- Services -->
<section class="services-section" id="services">
	<div class="container">
		<div class="row justify-content-center g-4">
			<div class="col-md-4">
				<div class="service-item">
					<div class="icon-box">
						<i class="bi bi-search"></i>
					</div>
					<h4>SEO Optimization</h4>
					<p>
						Improve visibility, rank higher in search, and turn traffic into measurable growth.
					</p>
				</div>
			</div>

			<div class="col-md-4">
				<div class="service-item">
					<div class="icon-box">
						<i class="bi bi-share-fill"></i>
					</div>
					<h4>Social Media Management</h4>
					<p>
						Build brand momentum with content, engagement, and campaigns tailored to your audience.
					</p>
				</div>
			</div>

			<div class="col-md-4">
				<div class="service-item">
					<div class="icon-box">
						<i class="bi bi-cursor-fill"></i>
					</div>
					<h4>PPC Campaigns</h4>
					<p>
						Launch targeted ad campaigns that attract qualified leads and drive stronger conversions.
					</p>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Proven Results -->
<section class="results-section">
	<div class="container">
		<h2 class="section-title">Proven Results</h2>

		<div class="row g-4">
			<div class="col-md-6 col-lg-4">
				<div class="case-card">
					<div class="card-img-wrap">
						<img
								src="/resources/landers/ctpupgrade/images/column1.png"
								alt="Case Study 1"
						/>
					</div>
					<div class="card-body">
						<h5><span>SEO Visibility Breakthrough:</span> 5X Traffic Growth</h5>
						<!--<a href="#" class="read-more-link">
							Read More <i class="bi bi-arrow-right"></i>
						</a>-->
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-4">
				<div class="case-card">
					<div class="card-img-wrap">
						<img
								src="/resources/landers/ctpupgrade/images/column2.png"
								alt="Case Study 2"
						/>
					</div>
					<div class="card-body">
						<h5><span>Lead Generation Boost:</span> 220% Increase</h5>
						<!--<a href="#" class="read-more-link">
							Read More <i class="bi bi-arrow-right"></i>
						</a>-->
					</div>
				</div>
			</div>

			<div class="col-md-6 col-lg-4">
				<div class="case-card">
					<div class="card-img-wrap">
						<img
								src="/resources/landers/ctpupgrade/images/column3.png"
								alt="Case Study 3"
						/>
					</div>
					<div class="card-body">
						<h5><span>E-commerce Surge:</span> 300% Growth Rate</h5>
						<!--<a href="#" class="read-more-link">
							Read More <i class="bi bi-arrow-right"></i>
						</a>-->
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Footer -->
<div class="footer-top-bar"></div>

<footer class="site-footer" id="contact">
	<div class="container">
		<div class="row g-4 align-items-center flex-column justify-content-center">
			<div class="col-12 text-center">
				<div class="footer-logo">
					<img
							src="/resources/landers/ctpupgrade/images/logo-no-bg.png"
							alt="Resurge Alliance Footer Logo"
					/>
				</div>
			</div>

			<div class="col-12">
				<div class="footer-bottom">
					© 2026 Resurge Alliance. All Rights Reserved.
				</div>
			</div>
		</div>

	</div>
</footer>

<!-- Bootstrap JS -->
<script
		src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>