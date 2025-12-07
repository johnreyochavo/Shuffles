<?php

// Include database connection

include 'db_connect.php';



$message = "";

$msg_type = "";



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs

    $full_name = $conn->real_escape_string($_POST['full_name']);

    $email = $conn->real_escape_string($_POST['email']);

    $phone = $conn->real_escape_string($_POST['phone']);

    $reserve_date = $conn->real_escape_string($_POST['reserve_date']);

    $reserve_time = $conn->real_escape_string($_POST['reserve_time']);

    $guests = intval($_POST['guests']);

    $special_request = $conn->real_escape_string($_POST['special_request']);



    // Insert Query

    $sql = "INSERT INTO reservations (full_name, email, phone, reserve_date, reserve_time, guests, special_request) 

            VALUES ('$full_name', '$email', '$phone', '$reserve_date', '$reserve_time', '$guests', '$special_request')";



    if ($conn->query($sql) === TRUE) {

        $message = "Reservation successful! We look forward to seeing you.";

        $msg_type = "success";

    } else {

        $message = "Error: " . $sql . "<br>" . $conn->error;

        $msg_type = "error";

    }

}

$conn->close();

?>



<!DOCTYPE html>

<html lang="en">



<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Reservation - Shuffles Resto Bar</title>

  <link rel="stylesheet" href="styles.css">

  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />

  

  <!-- Embedded Improved CSS for Reservation Form -->

  <style>

    /* Header & Footer Styles (Kept from your reserv.css to ensure consistency) */

    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap");



    * { margin: 0; padding: 0; box-sizing: border-box; scroll-behavior: smooth; }

    

    /* --- HEADER STYLES --- */

    .header { position: fixed; top: 0; left: 0; width: 100%; padding: 1.3rem 10%; display: flex; justify-content: space-between; align-items: center; z-index: 100; height: 70px; background-color: rgba(0, 0, 0, 0.7); }

    .header::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }

    .header::after{ content: ''; position: absolute; top: 0; left: -110%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .4), transparent); transition: .5s; }

    .header:hover:after { left: 100%; }

    .logo { font-size: 2.5rem; color: #d62828; text-decoration: none; font-weight: 700; }

    .logo img { height: 280px; width: auto; display: block; transform: translateX(-100px); margin-left: 60px; }

    .navbar a { position: relative; font-size: 1.15rem; color: #fff; text-decoration: none; margin-left: 40px; transition: color 0.3s ease; }

    .navbar a::after, .navbar a::before { content: ""; position: absolute; width: 0%; height: 2px; background: #d62828; transition: width 0.3s ease, box-shadow 0.3s ease; border-radius: 2px; }

    .navbar a::after { left: 0; bottom: -6px; }

    .navbar a::before { right: 0; top: -6px; }

    .navbar a:hover::after, .navbar a:hover::before { width: 100%; box-shadow: 0 0 8px #d62828; }

    #check { display: none; }

    .icons { position: absolute; right: 5%; font-size: 2.8rem; color: #fff; cursor: pointer; display: none; }



    /* --- IMPROVED RESERVATION CSS --- */

    .reservation {

      background: #1a1a1a;

      color: #f5f5f5;

      padding: 8rem 2rem;

      min-height: 100vh;

      display: flex;

      align-items: center;

      justify-content: center;

    }



    .reservation-container {

      width: 100%;

      max-width: 800px;

      background: #111;

      padding: 3rem;

      border-radius: 15px;

      box-shadow: 0 0 20px rgba(0,0,0,0.5);

      border: 1px solid #333;

    }



    .reservation h2 {

      font-size: 2.5rem;

      color: #d62828;

      text-align: center;

      margin-bottom: 0.5rem;

      text-transform: uppercase;

      letter-spacing: 1px;

    }



    .reservation-subtitle {

      text-align: center;

      color: #aaa;

      margin-bottom: 2.5rem;

      font-size: 1rem;

    }



    /* Alert Message */

    .alert {

      padding: 15px;

      margin-bottom: 20px;

      border-radius: 5px;

      text-align: center;

      font-weight: 600;

    }

    .alert.success { background-color: #155724; color: #d4edda; border: 1px solid #c3e6cb; }

    .alert.error { background-color: #721c24; color: #f8d7da; border: 1px solid #f5c6cb; }



    .reservation-form {

      display: grid;

      grid-template-columns: 1fr 1fr;

      gap: 1.5rem;

    }



    .input-wrapper {

      position: relative;

      width: 100%;

    }



    .input-wrapper i {

      position: absolute;

      left: 15px;

      top: 50%;

      transform: translateY(-50%);

      color: #d62828;

      font-size: 1.1rem;

    }



    .reservation-form input, 

    .reservation-form textarea,

    .reservation-form select {

      width: 100%;

      padding: 1rem 1rem 1rem 3rem; /* Space for icon */

      border-radius: 8px;

      border: 1px solid #333;

      background: #1a1a1a;

      color: #f5f5f5;

      font-size: 1rem;

      transition: all 0.3s ease;

    }



    /* Remove default calendar icon on dark mode if needed, but usually okay */

    ::-webkit-calendar-picker-indicator { filter: invert(1); }



    .reservation-form input:focus,

    .reservation-form textarea:focus,

    .reservation-form select:focus {

      border-color: #d62828;

      box-shadow: 0 0 10px rgba(214, 40, 40, 0.2);

      outline: none;

    }



    /* Spanning columns */

    .full-width { grid-column: span 2; }



    .btn-container {

      grid-column: span 2;

      text-align: center;

      margin-top: 1rem;

    }



    .reservation .btn {

      padding: 1rem 3rem;

      border-radius: 50px;

      background-color: transparent;

      color: #d62828;

      font-weight: bold;

      font-size: 1.1rem;

      border: 2px solid #d62828;

      cursor: pointer;

      transition: 0.3s ease;

      text-transform: uppercase;

      letter-spacing: 1px;

    }



    .reservation .btn:hover {

      background: #d62828;

      color: #fff;

      box-shadow: 0 0 20px rgba(214, 40, 40, 0.6);

    }



    /* --- FOOTER STYLES --- */

    .footer { background: #0a0a0a; color: #f5f5f5; padding: 4rem 2rem 2rem; }

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

      

      .reservation-form { grid-template-columns: 1fr; }

      .full-width { grid-column: span 1; }

      .btn-container { grid-column: span 1; }

      .footer-container { flex-direction: column; text-align: center; align-items: center; }

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



  <!-- Reservation Section -->

  <section class="reservation" id="reservation">

    <div class="reservation-container">

      <h2>Reserve a Table</h2>

      <p class="reservation-subtitle">Book your spot and enjoy an unforgettable night with us</p>



      <!-- PHP Success/Error Message Display -->

      <?php if ($message != ""): ?>

        <div class="alert <?php echo $msg_type; ?>">

            <?php echo $message; ?>

        </div>

      <?php endif; ?>



      <!-- Improved Form -->

      <form class="reservation-form" method="POST" action="reservation.php">

        

        <!-- Name -->

        <div class="input-wrapper full-width">

          <i class="fa-solid fa-user"></i>

          <input type="text" name="full_name" placeholder="Full Name" required>

        </div>



        <!-- Email -->

        <div class="input-wrapper">

          <i class="fa-solid fa-envelope"></i>

          <input type="email" name="email" placeholder="Email Address" required>

        </div>



        <!-- Phone -->

        <div class="input-wrapper">

          <i class="fa-solid fa-phone"></i>

          <input type="tel" name="phone" placeholder="Phone Number" required>

        </div>



        <!-- Date -->

        <div class="input-wrapper">

          <i class="fa-solid fa-calendar-days"></i>

          <input type="date" name="reserve_date" required>

        </div>



        <!-- Time -->

        <div class="input-wrapper">

          <i class="fa-solid fa-clock"></i>

          <input type="time" name="reserve_time" required>

        </div>



        <!-- Guests -->

        <div class="input-wrapper full-width">

          <i class="fa-solid fa-users"></i>

          <input type="number" name="guests" min="1" max="50" placeholder="Number of Guests" required>

        </div>



        <!-- Special Requests -->

        <div class="input-wrapper full-width">

          <i class="fa-solid fa-pen-to-square"></i>

          <textarea name="special_request" rows="3" placeholder="Special Requests (e.g., Birthday, Allergies)"></textarea>

        </div>



        <!-- Button -->

        <div class="btn-container">

          <button type="submit" class="btn">Confirm Reservation</button>

        </div>



      </form>



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