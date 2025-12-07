<?php

include 'db_connect.php';



$msg = "";

$msg_class = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $conn->real_escape_string($_POST['name']);

    $email = $conn->real_escape_string($_POST['email']);

    $subject = $conn->real_escape_string($_POST['subject']);

    $message = $conn->real_escape_string($_POST['message']);



    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";



    if ($conn->query($sql) === TRUE) {

        $msg = "Message sent successfully! We will get back to you soon.";

        $msg_class = "success";

    } else {

        $msg = "Error: " . $conn->error;

        $msg_class = "error";

    }

}

$conn->close();

?>



<!DOCTYPE html>

<html lang="en">



<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Contact Us - Shuffles Resto Bar</title>

  <link rel="stylesheet" href="styles.css">

  <!-- We embed the contact specific css below to ensure it overrides defaults -->

  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />



  <style>

    /* Paste contents of cont.css here, or keep your external file. 

       I have added the NEW STYLES for the form below your existing CSS structure. */

    

    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap");



    * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth;}



    /* HEADER STYLES (Untouched) */

    .header { position: fixed; top: 0; left: 0; width: 100%; padding: 1.3rem 10%; display: flex; justify-content: space-between; align-items: center; z-index: 100; height: 70px; background-color: rgba(0, 0, 0, 0.7); }

    .header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }

    .header::after{ content: ''; position: absolute; top: 0; left: -110%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .4), transparent); transition: .5s; }

    .header:hover:after { left: 100%; }

    .logo { font-size: 2.5rem; color: #d62828; text-decoration: none; font-weight: 700; }

    .logo img { height: 280px; width: auto; display: block; transform: translateX(-100px); margin-left: 60px; }

    .navbar a { position: relative; font-size: 1.15rem; color: #fff; text-decoration: none; margin-left: 40px; transition: color 0.3s ease; }

    .navbar a::after { content: ""; position: absolute; left: 0; bottom: -6px; width: 0%; height: 2px; background: #d62828; transition: width 0.3s ease, box-shadow 0.3s ease; border-radius: 2px; }

    .navbar a::before { content: ""; position: absolute; right: 0; top: -6px; width: 0%; height: 2px; background: #d62828; transition: width 0.3s ease, box-shadow 0.3s ease; border-radius: 2px; }

    .navbar a:hover::after, .navbar a:hover::before { width: 100%; box-shadow: 0 0 8px #d62828; }

    #check{ display: none; }

    .icons{ position: absolute; right: 5%; font-size: 2.8rem; color: #fff; cursor: pointer; display: none; }

    

    /* --- IMPROVED CONTACT SECTION CSS --- */

    .contact {

      background: #1a1a1a;

      color: #f5f5f5;

      padding: 8rem 2rem 4rem; /* Adjusted top padding for fixed header */

      min-height: 100vh;

    }



    .contact-container {

      max-width: 1200px;

      margin: 0 auto;

    }



    .contact-header {

      text-align: center;

      margin-bottom: 3rem;

    }



    .contact-header h2 {

      font-size: 3rem;

      color: #d62828;

      margin-bottom: 0.5rem;

    }



    .contact-subtitle {

      font-size: 1.1rem;

      color: #ccc;

    }



    /* Grid Layout for Form + Info */

    .contact-wrapper {

      display: grid;

      grid-template-columns: 1fr 1fr;

      gap: 3rem;

      align-items: start;

    }



    /* Form Styles */

    .contact-form-box {

      background: #111;

      padding: 2.5rem;

      border-radius: 15px;

      box-shadow: 0 5px 15px rgba(0,0,0,0.5);

      border: 1px solid #333;

    }



    .form-group {

      margin-bottom: 1.5rem;

      position: relative;

    }



    .form-group input, 

    .form-group textarea {

      width: 100%;

      padding: 1rem 1rem 1rem 3rem; /* space for icon */

      background: #222;

      border: 1px solid #444;

      color: #fff;

      border-radius: 8px;

      font-size: 1rem;

      outline: none;

      transition: 0.3s;

    }



    .form-group i {

      position: absolute;

      left: 15px;

      top: 18px;

      color: #d62828;

    }



    .form-group input:focus, 

    .form-group textarea:focus {

      border-color: #d62828;

      box-shadow: 0 0 8px rgba(214, 40, 40, 0.3);

    }



    .btn-submit {

      width: 100%;

      padding: 1rem;

      background: transparent;

      color: #d62828;

      border: 2px solid #d62828;

      font-weight: bold;

      font-size: 1.1rem;

      border-radius: 30px;

      cursor: pointer;

      transition: 0.3s;

      text-transform: uppercase;

    }



    .btn-submit:hover {

      background: #d62828;

      color: #fff;

      box-shadow: 0 0 15px #d62828;

    }



    /* Contact Details & Map (Right Side) */

    .info-map-container {

      display: flex;

      flex-direction: column;

      gap: 2rem;

    }



    .contact-details {

      background: #111;

      padding: 2rem;

      border-radius: 15px;

      border: 1px solid #333;

    }



    .contact-details ul { list-style: none; }

    .contact-details li { margin-bottom: 1.2rem; font-size: 1rem; color: #ddd; display: flex; gap: 10px; }

    .contact-details strong { color: #d62828; min-width: 80px; }



    .contact-map {

      width: 100%;

      height: 300px;

      border-radius: 15px;

      overflow: hidden;

      border: 2px solid #333;

    }

    .contact-map iframe { width: 100%; height: 100%; border: none; }



    /* Messages */

    .alert { padding: 15px; text-align: center; border-radius: 5px; margin-bottom: 20px; }

    .alert.success { background: #155724; color: #d4edda; border: 1px solid #c3e6cb; }

    .alert.error { background: #721c24; color: #f8d7da; border: 1px solid #f5c6cb; }



    /* FOOTER (Untouched) */

    .footer { background: #0a0a0a; color: #f5f5f5; padding: 4rem 2rem 2rem; position: relative; }

    .footer-container { max-width: 1200px; margin: 0 auto; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 2rem; border-bottom: 1px solid #333; padding-bottom: 2rem; }

    .footer-logo h2 { color: #d62828; font-size: 1.8rem; margin-bottom: 0.5rem; }

    .footer-logo p { font-size: 1rem; color: #bbb; }

    .socials { display: flex; gap: 1rem; align-items: center; }

    .socials a { text-decoration: none; color: #ffffff; border: 1px solid #5e0e15; padding: 0.5rem 1rem; border-radius: 25px; transition: 0.3s ease; }

    .socials a:hover { background: #5e0e15; color: #ffffff; box-shadow: 0 0 15px rgba(134, 132, 132, 0.6); }

    .footer-bottom { text-align: center; font-size: 0.9rem; color: #888; margin-top: 1.5rem; }



    /* Responsive */

    @media (max-width: 1180px) { .header{ padding: 1.3rem 5%; } }

    @media (max-width: 1180px) {

      .icons{ display:flex; }

      #check:checked~.icons #menu-icon{ display: none; }

      .icons #close-icon{ display: none; }

      #check:checked~.icons #close-icon{ display: block; }

      .navbar { position: absolute; top: 100%; left: 0; width: 100%; height: 0;  background-color: #4e1313af; overflow: hidden; transition: .3s ease;  }

      #check:checked~.navbar{ height: 20.5rem; }

      .navbar a { display: block; font-size: 1.1rem; margin: 1.5rem; text-align: center; transform: translateY(-50px); opacity: 0; transition: .3s ease; }

      #check:checked~.navbar a { transform: translateY(0); opacity: 1; transition-delay: calc(.10s *var(--i)); }

      

      .contact-wrapper { grid-template-columns: 1fr; }

      .footer-container { flex-direction: column; text-align: center; align-items: center; }

      .footer-bottom { position: static; transform: none; padding-top: 1rem; margin-top: 1.5rem; }

    }

  </style>

</head>



<body>



  <!-- Header Section -->

  <header class="header">

    <a href="index.html" class="logo">

      <img src="shapol_logo.png" alt="Shuffles Logo">

    </a>



    <input type="checkbox" id="check">

    <label for="check" class="icons">

      <i class='bx bx-menu' id="menu-icon"></i>

      <i class='bx bx-x' id="close-icon"></i>

    </label>

    

    <!-- Navigation -->

    <nav class="navbar">

      <a href="index.html" style="--i:0;">Home</a>

      <a href="prac_menu.html" style="--i:1;">Menu</a>

      <a href="index.html#event" style="--i:2;">Events</a>

      <a href="new-about.html" style="--i:3;">About</a>

      <a href="reservation.php" style="--i:4">Reservation</a>

      <a href="contact.php" style="--i:5;">Contact</a>

      <a href="gallery.html" style="--i:6;">Gallery</a>

    </nav>

  </header>



  <!-- Improved Contact Section -->

  <section class="contact" id="contact">

    <div class="contact-container">



      <div class="contact-header">

        <h2>Contact & Location</h2>

        <p class="contact-subtitle">Get in touch with us or find your way to great food.</p>

      </div>



      <!-- Alert Message -->

      <?php if ($msg != ""): ?>

        <div class="alert <?php echo $msg_class; ?>">

           <?php echo $msg; ?>

        </div>

      <?php endif; ?>



      <div class="contact-wrapper">

        

        <!-- LEFT: Contact Form -->

        <div class="contact-form-box">

          <form action="contact.php" method="POST">

            

            <div class="form-group">

              <i class="fa-solid fa-user"></i>

              <input type="text" name="name" placeholder="Your Name" required>

            </div>



            <div class="form-group">

              <i class="fa-solid fa-envelope"></i>

              <input type="email" name="email" placeholder="Email Address" required>

            </div>



            <div class="form-group">

              <i class="fa-solid fa-tag"></i>

              <input type="text" name="subject" placeholder="Subject (Optional)">

            </div>



            <div class="form-group">

              <i class="fa-solid fa-message"></i>

              <textarea name="message" rows="5" placeholder="Your Message..." required></textarea>

            </div>



            <button type="submit" class="btn-submit">Send Message</button>

          </form>

        </div>



        <!-- RIGHT: Info & Map -->

        <div class="info-map-container">

          

          <div class="contact-details">

            <ul>

              <li>

                <strong><i class="fa-solid fa-location-dot"></i> Address:</strong> 

                <span>Pueblo De Panay, Roxas City Capiz, Philippines</span>

              </li>

              <li>

                <strong><i class="fa-solid fa-phone"></i> Phone:</strong> 

                <span>+63 912 345 6789</span>

              </li>

              <li>

                <strong><i class="fa-solid fa-envelope"></i> Email:</strong> 

                <span>shuffles@adventureaxis.ph</span>

              </li>

              <li>

                <strong><i class="fa-solid fa-clock"></i> Hours:</strong>

                <span>

                  Tue & Thu: 11:00 AM - 10 PM<br>

                  Fri: 11:00 AM - 1 AM<br>

                  Sat: 11:00 AM - 1 AM<br>

                  Sun: 11:00 AM - 10 PM<br>

                  (Mon: Closed)

                </span>

              </li>

            </ul>

          </div>



          <div class="contact-map">

            <iframe

              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3908.91510367577!2d122.74544497584448!3d11.557943644291008!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33a5f34a1a829da7%3A0xd80badeedebbb6e4!2sShuffles!5e0!3m2!1sen!2sph!4v1757932577323!5m2!1sen!2sph"

              allowfullscreen=""

              loading="lazy"

              referrerpolicy="no-referrer-when-downgrade">

            </iframe>

          </div>



        </div> <!-- End Info Map Container -->



      </div> <!-- End Contact Wrapper -->



    </div>

  </section>



  <!-- Footer Section -->

  <footer class="footer">

    <div class="footer-container">



      <!-- Logo / Brand -->

      <div class="footer-logo">

        <h2>

          <a href="index.html" class="logo" style="font-size: 1.8rem; color: #d62828; text-decoration: none;">Shuffles Resto Bar</a>

        </h2>

        <p>Good Food. Great Drinks. Amazing Vibes.</p>

      </div>

      



       <!-- Social Media Links -->

    <div class="socials">

      <a href="https://web.facebook.com/shuffles.roxas" target="_blank">

        <i class="fa-brands fa-facebook-f"></i>

      </a>

      <a href="https://www.instagram.com/shuffles.roxas/p/DOKoaMkCYIQ/" target="_blank">

        <i class="fab fa-instagram"></i>

      </a>

      <a href="#">

        <i class="fab fa-tiktok"></i>

      </a>

    </div>



    </div>



      <div class="footer-bottom">

        <p>&copy; 2025 Shuffles Resto Bar | Designed by Reos John Pieza</p>

      </div>

  </footer>



</body>

</html>