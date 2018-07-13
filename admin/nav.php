<!-- Written by James Ding and Ibrahim Hawari
	Last Edited on 12/5/2014 -->

    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="admin.php">UNC Writing Center Scheduler</a>
        </div>
        <!-- /.navbar-header -->
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <a href="../common/logout.php"><span class="glyphicon glyphicon-off"></span></a>
            </li>
        </ul>
        <!-- /.navbar-top-links -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="run_algorithm.php" id="create_b"><i class="fa fa-magic fa-fw"></i> Run Algorithm</a>
                    </li>
                                      <li>
                      <a href="view_master.php" id="view_b"><i class ="fa fa-table fa-fw"></i> Master Schedule</a>
                  </li>
                    <li>
                      <a href="edit_writing_center_hours.php" id="view_b"><i class ="fa fa-clock-o fa-fw"></i> Manage Open Hours</a>
                  </li>
                  <li>
                    <a href="edit_employees.php" id="view_b"><i class ="fa fa-user fa-fw"></i> Manage Employees</a>
                  </li>
                  <li>
                    <a href="view_tutors.php" id="view_b"><i class="fa fa-thumbs-o-up fa-fw"></i> Tutor Preferences</a>
                </li>
                <li>
                    <a href="tutor.php" id="view_b"><i class="fa fa-edit fa-fw"></i> Tutor View</a>
                </li>
                <li>
                    <a href="help.php" id="help_b"><i class="fa fa-question-circle fa-fw"></i> Help</a>
                </li>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
</nav>

<!--Make sidebar buttons active upon click-->
<script type="text/javascript">
var selector, elems, makeActive;
selector = '.nav a';
elems = document.querySelectorAll(selector);

makeActive = function () {
    for (var i = 0; i < elems.length; i++)
     elems[i].classList.remove('active');
 this.classList.add('active');
};
for (var i = 0; i < elems.length; i++)
    elems[i].addEventListener('mousedown', makeActive);
</script>
