<div class="sidebar-container">
  <div class="sidebar-overlay">
    <div class="sidebar">
      <div class="close-btn"></div>
      <ul class="list-unstyled">
        <li>
          <div class="w-100 px-2 my-3"><strong><?php echo $_SESSION['name']; ?></strong></div>
        </li>
        <li>
          <a class="" href="?type=free_places">
            <div class="icon"><i class="fas fa-archway"></i></div>
            <div class="text">free places</div>
          </a>
        </li>
        <li>
          <a class="" href="?type=load_eng">
            <div class="icon"><i class="fab fa-wpbeginner"></i></div>
            <div class="text">Load Engineers</div>
          </a>
        </li>
        <li>
          <a class="" href="?type=load_dr">
            <div class="icon"><i class="fas fa-user-tie"></i></div>
            <div class="text">Load Professors</div>
          </a>
        </li>
        <li>
          <a class="" href="logout.php">
            <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
            <div class="text">Logout</div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>