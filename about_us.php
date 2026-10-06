<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EcoConnect - About Us</title>
    <link rel="icon" type="image/jpg" sizes="32x32" href="favicon-32x32.jpg" />
    <link rel="icon" type="image/jpg" sizes="16x16" href="favicon-16x16.jpg" />
    <link rel="stylesheet" href="stylesheet_style.css" />
    <style>
      body {
        margin: 0;
        font-family: sans-serif;
        background: 
          linear-gradient(rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.1)),
          url('images/background-leaves.jpg') center/cover no-repeat fixed;
        color: #333;
      }

      main.container {
        max-width: 900px;
        margin: 40px auto 80px;
        background-color: rgba(255, 255, 255, 0.75);
        border-radius: 10px;
        padding: 30px 40px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
      }

      main.container h1,
      main.container h2 {
        color: #2e7d32;
        margin-bottom: 20px;
        text-align: center;
      }

      main.container p {
        line-height: 1.6;
        font-size: 18px;
        margin-bottom: 20px;
        text-align: justify;
      }

      ul.values-list {
        list-style: disc inside;
        max-width: 700px;
        margin: 0 auto 40px auto;
        font-size: 18px;
        color: #33691e;
      }

      ul.values-list li {
        margin-bottom: 12px;
      }

      /* Team Section */
      .team {
        margin-top: 60px;
      }

      .team-members {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 40px;
      }

      .member {
        width: 200px;
        background: rgba(241, 248, 233, 0.9);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      }

      .member img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
        border: 3px solid #2e7d32;
      }

      .member h3 {
        margin: 0 0 8px 0;
        color: #33691e;
        font-size: 20px;
      }

      .member p.title {
        font-weight: 600;
        color: #558b2f;
        margin: 0 0 6px 0;
      }

      .member p.role {
        font-size: 14px;
        color: #4a4a4a;
      }

      /* Timeline Section */
      .timeline {
        margin-top: 60px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 20px;
        border-left: 3px solid #2e7d32;
      }

      .timeline-event {
        margin-bottom: 30px;
        position: relative;
        padding-left: 30px;
      }

      .timeline-event::before {
        content: '';
        position: absolute;
        left: -12px;
        top: 4px;
        width: 20px;
        height: 20px;
        background-color: #2e7d32;
        border-radius: 50%;
        box-shadow: 0 0 5px rgba(0,0,0,0.15);
      }

      .timeline-event h3 {
        margin: 0 0 5px 0;
        color: #33691e;
      }

      .timeline-event time {
        font-size: 14px;
        color: #558b2f;
        font-weight: 600;
      }

      .timeline-event p {
        margin: 5px 0 0 0;
        color: #4a4a4a;
        font-size: 16px;
      }

      /* Responsive adjustments */
      @media (max-width: 650px) {
        .team-members {
          flex-direction: column;
          align-items: center;
        }

        main.container {
          padding: 20px 15px;
          margin: 20px auto 60px;
        }
      }
    </style>
  </head>
  <body>
    <header>
      <div class="container">
        <div class="logo">
          <img src="images/logo.jpg" alt="EcoConnect Logo" />
          <h1>EcoConnect</h1>
        </div>
        <nav>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="about_us.php" class="active">About Us</a></li>
          </ul>
        </nav>
        <div class="user-actions">
          <a href="auth/login.php" class="btn btn-secondary">Login</a>
          <a href="auth/register.php" class="btn btn-primary">Register</a>
        </div>
      </div>
    </header>

    <main class="container">
      <h1>About EcoConnect</h1>
      <p>
        At EcoConnect, we are dedicated to providing innovative, reliable, and efficient waste management solutions that benefit our communities and the environment. Founded with a vision to promote sustainable living, we specialize in waste collection services that cater to residential, commercial, and industrial needs.
      </p>
      <p>
        Our mission is to bridge the gap between waste producers and recycling facilities by offering a seamless, technology-driven platform that connects users to responsible waste collection services. We believe in empowering individuals and organizations to make eco-friendly choices that contribute to a cleaner and healthier planet.
      </p>

      <h2>Our Values</h2>
      <ul class="values-list">
        <li><strong>Sustainability:</strong> Committed to reducing waste and promoting recycling for a greener future.</li>
        <li><strong>Innovation:</strong> Utilizing the latest technology to enhance waste collection efficiency and user experience.</li>
        <li><strong>Community Focus:</strong> Working closely with local communities to address their specific waste management needs.</li>
        <li><strong>Integrity:</strong> Ensuring transparency and accountability in all our operations.</li>
      </ul>

      <h2>Why Choose EcoConnect?</h2>
      <p>
        With EcoConnect, you gain access to a professional, dependable service that prioritizes customer satisfaction and environmental responsibility. Our user-friendly platform allows you to schedule pickups, track waste types, and learn about proper waste disposal practices.
      </p>
      <p>
        Join us in our commitment to making waste management smarter, simpler, and more sustainable.
      </p>

      <!-- Team Members Section -->
      <section class="team">
        <h2>Meet Our Team</h2>
        <div class="team-members">
          <div class="member">
            <img src="images/team/ceo.jpeg" alt="Alexander Smith" />
            <h3>Giselle UWAJENEZA</h3>
            <p class="title">Founder & CEO</p>
            <p class="role">
              Giselle leads EcoConnect with a vision to innovate sustainable waste management through cutting-edge technology and community collaboration.
            </p>
          </div>
          <div class="member">
            <img src="images/team/manager.jpeg" alt="Maria Lopez" />
            <h3>Jemima DUSENAYO</h3>
            <p class="title">Chief Operations Officer</p>
            <p class="role">
              Jemima oversees daily operations, ensuring efficient service delivery and maintaining quality across all waste collection processes.
            </p>
          </div>
          <div class="member">
            <img src="images/team/tuza.jpeg" alt="Leo Kim" />
            <h3>Denise TUZA</h3>
            <p class="title">Technology Officer</p>
            <p class="role">
              Denise drives the technological development of our platform, focusing on innovation, user experience, and system reliability.
            </p>
          </div>
          <div class="member">
            <img src="images/team/adede.jpeg" alt="Amina Hassan" />
            <h3>Adeline TURIKUMWENAYO</h3>
            <p class="title">Community Manager</p>
            <p class="role">
              Adeline builds strong relationships with local communities and stakeholders to promote awareness and engagement.
            </p>
          </div>
          <div class="member">
            <img src="images/team/sifa.jpeg" alt="Amina Hassan" />
            <h3>Sifa Innocente MUKIZA </h3>
            <p class="title">Community Manager</p>
            <p class="role">
              Sifa builds strong relationships with local communities and stakeholders to promote awareness and engagement.
            </p>
          </div>
          <div class="member">
            <img src="images/team/jax.jpeg" alt="Amina Hassan" />
            <h3>Jackson RUKUNDO</h3>
            <p class="title">Community Manager</p>
            <p class="role">
              Jackson builds strong relationships with local communities and stakeholders to promote awareness and engagement.
            </p>
          </div>
        </div>
      </section>

      <!-- Timeline Section -->
      <section class="timeline">
        <h2>Our Journey</h2>
        <div class="timeline-event">
          <time>2018</time>
          <h3>Company Founded</h3>
          <p>EcoConnect was established with a mission to improve waste management using technology and sustainable practices.</p>
        </div>
        <div class="timeline-event">
          <time>2019</time>
          <h3>Launched First Waste Collection Service</h3>
          <p>Started operations in key urban areas, connecting residents with responsible waste collectors.</p>
        </div>
        <div class="timeline-event">
          <time>2021</time>
          <h3>Expanded Service Coverage</h3>
          <p>Scaled up to include commercial and industrial clients, improving service efficiency and coverage.</p>
        </div>
        <div class="timeline-event">
          <time>2023</time>
          <h3>Introduced Tech Platform</h3>
          <p>Launched our mobile and web platforms allowing users to schedule pickups and track their waste responsibly.</p>
        </div>
        <div class="timeline-event">
          <time>2025</time>
          <h3>Ongoing Innovation & Growth</h3>
          <p>Continuing to expand our impact through new technologies and strategic partnerships.</p>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div class="footer-logo">
            <img src="images/logo.jpg" alt="EcoConnect Logo" />
            <h3>EcoConnect</h3>
            <p>Efficient waste management solutions</p>
          </div>
          <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="index.php">Home</a></li>
              <li><a href="contact.html">Contact</a></li>
              <li><a href="about_us.php">About Us</a></li>
            </ul>
          </div>
          <div class="footer-contact">
            <h4>Contact Us</h4>
            <p>Email: uwajenezagiselle@gmail.com</p>
            <p>Phone: +250780777252</p>
            <p>LinkedIn: Uwajeneza Giselle</p>
          </div>
        </div>
        <div class="footer-bottom">
          <p>&copy; 2025 EcoConnect. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </body>
</html>
