<?php
// Get a random subset of contributor institute logos
require "../vendor/autoload.php";
use Spyc;
$contributors = spyc_load_file('../nf-core-contributors.yaml');
$contributors_img_list = [];
foreach ($contributors['contributors'] as $idx => $c) {
  if (isset($c['image_fn']) and $c['image_fn'] and isset($c['full_name']) and $c['full_name']) {
    $card_id = preg_replace('/[^a-z]+/', '-', strtolower($c['full_name']));
    $img_path = 'assets/img/contributors-white/' . $c['image_fn'];
    if (file_exists($img_path)) {
      $contributors_img_list[] = '<a href="/community#' . $card_id . '"><img src="' . $img_path . '" data-placement="bottom" data-toggle="tooltip" title="' . $c['full_name'] . '"></a>';
    }
  }
}
// Shuffle and truncate the list
shuffle($contributors_img_list);

//Check if there is an event
$md_github_url = 'https://github.com/nf-core/nf-co.re/tree/master/markdown/events';
$header_btn_url = 'https://nf-co.re/events/rss';

# To get parse_md_front_matter() and sanitise_date_meta() functions
require_once('../includes/functions.php');

if($curr_event){
  // Shared function to prep nicely formatted output
  $curr_event['meta'] = prep_current_event($curr_event);
  // Dropdown button to visit event
  $curr_event['meta']['location_dropdown'] = '';
  if (array_key_exists('location_url', $curr_event) && $curr_event['ongoing']) {
    if(count($curr_event['location_url']) == 1){
      $url = $curr_event['location_url'];
      if($url[0] == "#") $url = $curr_event['url'] . $url;
      $m = $curr_event['meta']['location_url_meta'];

      $curr_event['meta']['location_dropdown'] = '<a class="btn btn-success mr-2 mb-2" href="'.$url.'">'.$m[0]['icon'].' Watch now</a>';
    } else {
      $curr_event['meta']['location_dropdown'] = '
        <div class="dropup mr-2 mb-2">
          <a class="btn btn-success dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Watch now
          </a>

          <div class="dropdown-menu">
          ';
      foreach ($curr_event['location_url'] as $idx => $url) {
        $m = $curr_event['meta']['location_url_meta'][$idx];
        if($url[0] == "#") $url = $curr_event['url'] . $url;
        $curr_event['meta']['location_dropdown'] .= '<a class="dropdown-item" href="'.$url.'" target="_blank">'.$m['icon'].' <code>'.$url.'</code></a>'."\n";
      }
      $curr_event['meta']['location_dropdown'] .= '</div></div>';
    }
  }
  // Countdown timer for upcoming events
  if(!$curr_event['ongoing']){
    $dtF = new \DateTime('@0');
    $dtT = new \DateTime("@".(time() - $curr_event['start_ts']));
    $dtDiff = $dtF->diff($dtT);
    if($dtDiff->format('%d') == '0'){
      $countdown_text = $dtDiff->format('%hh %Im %Ss');
    } else {
      $countdown_text = $dtDiff->format('%d days,<br>%hh %Im %Ss');
    }
    $curr_event['meta']['countdown'] = "
    <script type=\"text/javascript\">
        setInterval(function(){
          var eventTime = ".$curr_event['start_ts']." * 1000;
          var currentTime = Date.now();
          var delta = Math.abs(eventTime - currentTime) / 1000;

          var days = Math.floor(delta / 86400);
          delta -= days * 86400;

          var hours = Math.floor(delta / 3600) % 24;
          delta -= hours * 3600;

          var minutes = Math.floor(delta / 60) % 60;
          delta -= minutes * 60;

          var seconds = Math.floor(delta) % 60;

          $('.countdown').html((days > 0 ? days + ' day' + (days > 1 ? 's':'') + ',<br>' : '') + hours  + 'h ' + minutes + 'm ' + seconds +'s')
        }, 1000);
    </script>
    <h5 class=\"pt-4\">Event Countdown:</h5>
    <p class=\"display-4 text-nowrap countdown\">" . $countdown_text . "</p>
    ";
  }
}

$import_moment = true;
include('../includes/header.php');
?>

<div class="homepage-header">
  <?php if ($curr_event) : ?>
    <div class="mainpage-subheader-heading homepage-header-contents event-triangle-down">
      <div class="container-fluid text-left">
        <div class="row">
          <?php if ($curr_event['ongoing']) : ?>
            <div class="col-lg-3 overflow-hidden d-none d-lg-block">
              <i class="fad fa-broadcast-tower homepage-header-fa-background"></i>
              <h4 class="display-4 pt-2">Ongoing event</h4>
            </div>
            <div class="col-lg-3 pt-2 pb-1 mb-2 overflow-hidden d-lg-none mainpage-subheader-heading-header">
              <h5 class="pt-2 font-weight-light">Ongoing event</h5>
            </div>
          <?php else : ?>
            <div class="col-lg-3 overflow-hidden d-none d-lg-block">
              <i class="fad fa-alarm-clock homepage-header-fa-background"></i>
              <h4 class="display-4 pt-2">Upcoming event</h4>
            </div>
            <div class="col-lg-3 pt-2 pb-1 mb-2 overflow-hidden d-lg-none mainpage-subheader-heading-header">
              <h5 class="pt-2 font-weight-light">Upcoming event</h5>
            </div>
          <?php endif; ?>
          <div class="col pt-lg-3 pb-lg-3 text-center text-lg-left">
            <h5 class="pt-2 pb-0 pb-lg-1"><a href="<?php echo $curr_event['url']; ?>" class="text-success text-decoration-none"><?php echo $curr_event['title']; ?></a></h5>
            <p class="lead d-none d-sm-block"><a href="<?php echo $curr_event['url']; ?>" class="text-body text-decoration-none"><?php echo $curr_event['subtitle']; ?></a></p>
            <p class="d-sm-none mb-2"><a href="<?php echo $curr_event['url']; ?>" class="text-body text-decoration-none"><?php echo $curr_event['subtitle']; ?></a></p>
            <p class="d-none d-lg-block"><a href="<?php echo $curr_event['url']; ?>" class="text-secondary text-decoration-none" <?php echo $curr_event['meta']['nice_date_string'][0]; ?>><?php echo $curr_event['meta']['nice_date_string'][1]; ?></a><span class="d-none d-lg-inline"> &nbsp; <?php echo $curr_event['meta']['event_type_badge']; ?></span></p>
            <p class="d-lg-none small mb-2"><a href="<?php echo $curr_event['url']; ?>" class="text-secondary text-decoration-none" <?php echo $curr_event['meta']['nice_date_string'][0]; ?>><?php echo $curr_event['meta']['nice_date_string'][1]; ?></a><span class="d-none d-lg-inline"> &nbsp; <?php echo $curr_event['meta']['event_type_badge']; ?></span></p>
            <?php if ($curr_event['ongoing'] && isset($curr_event['youtube_embed'])) : ?>
              <div class="btn-toolbar justify-content-center justify-content-lg-start">
                <?php echo $curr_event['meta']['location_dropdown']; ?>
                <a href="<?php echo $curr_event['url']; ?>" class="btn btn-outline-success mb-2 d-none d-lg-inline-block">Event Details</a>
                <a href="<?php echo $curr_event['url']; ?>" class="btn btn-sm btn-outline-success mb-2 d-lg-none">Event Details</a>
              </div>
            <?php endif;
            if (!$curr_event['ongoing']) : ?>
              <a href="<?php echo $curr_event['url']; ?>" class="btn btn-outline-success mb-2 d-none d-lg-inline-block">Event Details</a>
              <a href="<?php echo $curr_event['url']; ?>" class="btn btn-sm btn-outline-success mb-2 d-lg-none">Event Details</a>
            <?php endif; ?>
          </div>
          <div class="col-lg-4 col-xl-3">
            <?php if ($curr_event['ongoing'] && isset($curr_event['youtube_embed'])) :
              if (!is_array($curr_event['youtube_embed'])) $curr_event['youtube_embed'] = [$curr_event['youtube_embed']];
              $video_id = get_youtube_id($curr_event['youtube_embed'][0]);
            ?>
              <div class="embed-responsive embed-responsive-16by9 mt-3 mb-5 d-none d-lg-block">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $video_id; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
              </div>
              <?php
              if (count($curr_event['youtube_embed']) > 1) : ?>
                <p class="text-right"><a href="<?php echo $curr_event['url']; ?>" class="btn btn-success my-2">Watch <?php echo count($curr_event['youtube_embed']) - 1;
                                                                                                                      d ?> more</a></p>
              <?php endif;
            elseif ($curr_event['ongoing']) : ?>
              <div class="pt-lg-5">
                <?php echo $curr_event['meta']['location_dropdown']; ?>
                <a href="<?php echo $curr_event['url']; ?>" class="btn btn-outline-success mb-2">Event Details</a>
              </div>
            <?php else : ?>
              <div class="d-none d-lg-block">
                <?php echo $curr_event['meta']['countdown']; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="homepage-header-contents col-md-5">
    <h1>
      <img src="assets/img/logo/nf-core-logo.svg" class="hide-dark">
      <img src="assets/img/logo/nf-core-logo-darkbg.svg" class="hide-light">
    </h1>
    <p class="lead font-weight-normal">A community effort to collect a curated set of analysis pipelines built using Nextflow.</p>
    <div class="hompage-cta-flex mb-5">
      <a class="hompage-cta" href="/pipelines">View Pipelines</a>
    </div>
  </div>
</div>
<form class="form-inline searchbar_form homepage_search" action="search" method="get">
  <input type="search" class="form-control" placeholder="Search" name="q" required>
  <button type="submit" class="btn btn-outline-success">Search</button>
</form>

<div class="triangle triangle-up"></div>
<div class="homepage-intro">
  <div class="container">
    <div class="row">
      <div class="col-md-4 py-3 py-md-5 px-md-4">
        <i class="fad fa-industry-alt fa-3x mb-2"></i>
        <h3 class="display-5 text-white">For facilities</h3>
        <p class="lead">Highly optimised pipelines with excellent reporting. Validated releases ensure reproducibility.</p>
      </div>
      <div class="col-md-4 py-3 py-md-5 px-md-4">
        <i class="fad fa-users fa-3x mb-2"></i>
        <h3 class="display-5 text-white">For users</h3>
        <p class="lead">Portable, documented and easy&nbsp;to&nbsp;use workflows.<br>Pipelines that you can trust.</p>
      </div>
      <div class="col-md-4 py-3 py-md-5 px-md-4">
        <i class="fad fa-laptop-code fa-3x mb-2"></i>
        <h3 class="display-5 text-white">For developers</h3>
        <p class="lead">Companion templates and tools help to validate your code and simplify common tasks.</p>
      </div>
    </div>
    <div class="text-center">
      <p class="d-inline-block text-white m-0 pb-3">
        <a class="text-white" href="https://doi.org/10.1038/s41587-020-0439-x" target="_blank">
          <img src="/assets/img/nature_biotech.svg" alt="Nature Biotechnology" width="50px" class="float-left mr-3">
          nf-core is published in Nature Biotechnology!
          <br>
          <em>Nat Biotechnol</em> <strong>38</strong>, 276–278 (2020).
          <img src="/assets/img/OpenAccess.svg" alt="Nature Biotechnology" height="20px" class="ml-3">
        </a>
      </p>
    </div>
  </div>
</div>
<div class="triangle triangle-down"></div>

<div class="container py-5 text-center lead" id="features">
  <p>Nextflow is an incredibly powerful and flexible workflow language.</p>
  <p><strong>nf-core</strong> pipelines adhere to strict guidelines - if one works, they all will.</p>
</div>

<div id="features" class="container homepage-feature-boxes pb-5">
  <h3 class="mb-4 text-center">Fully featured pipelines</h3>
  <div class="row">
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Documentation</h5>
          <img class="float-right ml-3" height="100px" src="assets/img/docs.svg" />
          <p class="card-text">Extensive documentation covering installation, usage and description of output files
            ensures that you won't be left in the dark.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">CI Testing</h5>
          <img class="float-right ml-3" height="90px" src="assets/img/github-actions.svg" />
          <p class="card-text">Every time a change is made to the pipeline code,
            nf-core pipelines use continuous-integration testing to ensure that nothing has broken.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Stable Releases</h5>
          <img class="float-right ml-3" height="100px" src="assets/img/releases.svg" />
          <p class="card-text">nf-core pipelines use GitHub releases to tag stable versions of the code
            and software, making pipeline runs totally reproducible.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Docker</h5>
          <img class="float-right ml-3" height="100px" src="assets/img/docker.svg" />
          <p class="card-text">Software dependencies are handled with docker containers
            which Nextflow downloads for you, so no need for any software installations.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Singularity</h5>
          <img class="float-right ml-3" height="100px" src="assets/img/singularity.svg" />
          <p class="card-text">If you're not able to use Docker, built-in support for Singularity can
            solve your HPC container problems. These are built from the docker containers.</p>
        </div>
      </div>
    </div>
    <div class="col-lg-4 mb-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Bioconda</h5>
          <img class="float-right ml-3" height="100px" src="assets/img/bioconda.svg" />
          <p class="card-text">Where possible, pipelines come with built-in bioconda support,
            so if you can't use software containers the dependencies can still be handled automatically.</p>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="bg-light py-5">
  <div id="developers" class="container homepage-feature-boxes">
    <h3 class="mb-4 text-center">Developers: Not just another registry</h3>
    <div class="row">
      <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Develop <u>with</u> the community</h5>
            <i class="fad fa-people-carry fa-5x float-right text-secondary ml-3"></i>
            <p class="card-text">Come and talk to us <em>before</em> you start writing a pipeline
              to find collaborators and check that your pipeline is suitable for nf-core.</p>
            <a href="join" class="btn btn-sm btn-outline-success arrow-hover"><span>Join Slack</span></a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Start from the template</h5>
            <i class="fas fa-magic fa-5x float-right text-secondary ml-3"></i>
            <p class="card-text">All pipelines must be based on our template
              and have a repo created using <code>nf-core create</code>. An automated sync
              keeps pipelines up to date.</p>
            <a href="developers/adding_pipelines" class="btn btn-sm btn-outline-success arrow-hover"><span>Read the docs</span></a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Start from the template</h5>
            <i class="fas fa-magic fa-5x float-right text-secondary ml-3"></i>
            <p class="card-text">All modules must be follow on consistency guidelines
              and made with <code>nf-core modules create</code></p>
            <a href="developers/adding_modules" class="btn btn-sm btn-outline-success arrow-hover"><span>Read the docs</span></a>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Collaborate, don't duplicate</h5>
            <i class="fad fa-code-merge fa-5x float-right text-secondary ml-3"></i>
            <p class="card-text">We only allow one pipeline per data type / analysis type.
              If a similar pipeline exists we'll ask you to add to that instead of making a new workflow.</p>
            <a href="developers/guidelines" class="btn btn-sm btn-outline-success arrow-hover"><span>See the guidelines</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-secondary py-5">
  <div class="container">

    <div class="row videos-row">
      <div class="col-md-6">
        <div class="embed-responsive embed-responsive-16by9 hidden-xs hidden-sm">
          <iframe id="nf-core-video" class="embed-responsive-item" src="https://www.youtube.com/embed/gUM9acK25tQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
      </div>
      <div class="col-md-6">
        <ul class="list-switch left video-chooser">
          <li><a href="https://youtu.be/gUM9acK25tQ" data-src="https://www.youtube.com/embed/gUM9acK25tQ" class="active"> Introduction to nf-core <em>(1:01)</em></a></li>
          <li><a href="https://youtu.be/cXBYusdjrc0" data-src="https://www.youtube.com/embed/cXBYusdjrc0"> Bytesize: How nf-core configs work <em>(15:00)</em></a></li>
          <li><a href="https://youtu.be/FFTNVbdD5pQ" data-src="https://www.youtube.com/embed/FFTNVbdD5pQ"> Bytesize: Pipeline code walkthrough <em>(20:00)</em></a></li>
          <li><a href="https://youtu.be/OvtCc855Vek" data-src="https://www.youtube.com/embed/OvtCc855Vek"> Tutorial: Running pipelines <em>(17:00)</em></a></li>
          <li><a href="https://youtu.be/-GcuxoIpfOc" data-src="https://www.youtube.com/embed/-GcuxoIpfOc"> BovReg: A longer introduction to nf-core <em>(45:00)</em></a></li>
          <li><a href="https://youtu.be/hCGuF9bA9ho" data-src="https://www.youtube.com/embed/hCGuF9bA9ho"> BovReg: nf-core pipelines <em>(60:00)</em></a></li>
          <li><a href="https://youtu.be/lUJ1L-qDeXM" data-src="https://www.youtube.com/embed/lUJ1L-qDeXM"> BovReg: Developing with nf-core <em>(50:00)</em></a></li>
        </ul>
      </div>
    </div>

  </div>
</div>

<div class="bg-dark py-5">
  <div class="container">
    <div class="row">
      <div class="col-sm-6">
        <h2 id="get-started" class="text-white">Get started in minutes</h2>
        <p class="text-white-50">Nextflow lets you run nf-core pipelines on virtually any computing environment.</p>
        <p class="text-white-50">Most nf-core genomics pipelines come with built-in support for
          <a href="https://ewels.github.io/AWS-iGenomes/" target="_blank" style="white-space:nowrap;">AWS-iGenomes</a>,
          with genome references for over 30 common species.
        </p>
        <p class="text-white-50">The nf-core companion tool makes it easy to list all available nf-core pipelines
          and shows which are available locally. Local versions are checked against the latest available release.</p>
      </div>
      <div class="col-sm-6">
        <div class="card text-white bg-dark border-light">
          <div class="card-body">
            <pre class="text-white mb-0">
<span class="text-white-50"># Install nextflow</span>
curl -s https://get.nextflow.io | bash
mv nextflow ~/bin/

<span class="text-white-50"># Launch the RNAseq pipeline</span>
nextflow run nf-core/rnaseq \
    --input samplesheet.csv \
    --genome GRCh37 \
    -profile docker

<span class="text-white-50"># Install nf-core tools</span>
pip install nf-core

<span class="text-white-50"># List all nf-core pipelines and show available updates</span>
nf-core list
</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container my-5 text-center">
  <p class="lead"></p>
  <div class="row">
    <div class="col-md-4 px-md-5 mb-5 mb-md-0">
      <h3>See what's available</h3>
      <p>Check out the available pipelines to see if we have what you need. Each comes with release details, keywords and a description.</p>
      <a class="btn btn-lg btn-success arrow-hover" href="/pipelines"><span><i class="fas fa-toolbox mr-1"></i> Available pipelines</span></a>
    </div>
    <div class="col-md-4 px-md-5 mb-5 mb-md-0">
      <h3>Run a pipeline</h3>
      <p>Read the quickstart tutorial to learn how to get set up with the required software and tools, and how to launch a nf-core pipeline.</p>
      <a class="btn btn-lg btn-success arrow-hover" href="/usage/introduction"><span><i class="fad fa-graduation-cap mr-1"></i> Quickstart Tutorial</span></a>
    </div>
    <div class="col-md-4 px-md-5 mb-5 mb-md-0">
      <h3>Get into the code</h3>
      <p>If you're interested in contributing to nf-core, take a look at the developer documentation to see what's required.</p>
      <a class="btn btn-lg btn-success arrow-hover" href="developers/guidelines"><span><i class="fad fa-code mr-1"></i> Developer docs</span></a>
    </div>
  </div>
</div>

<div id="community" class="homepage-usedby">
  <div class="container py-5">
    <h2>
      <a class="btn btn-success float-right d-none d-md-inline" href="/community#organisations">See a complete list &raquo;</a>
      <a href="/community#organisations">Used by groups all over the world</a>
    </h2>
    <p>The nf-core community is spread all over the globe and includes a large
      number of contributing users.</p>
    <p><a class="btn btn-success d-inline d-md-none" href="/community#organisations">See a complete list &raquo;</a></p>
    <div class="homepage_contrib_logos">
      <?php foreach ($contributors_img_list as $idx => $img) {
        // Hide images after 18 shown
        if ($idx > 16) echo str_replace('<a href', '<a style="display:none;" href', $img);
        else echo str_replace('<a href', '<a class="contrib_shown" href', $img);
      } ?>
    </div>
  </div>
</div>

<?php // Javascript for moment time zone support
if ($event['start_time']) {
  echo '
    <script type="text/javascript">
    $("[data-timestamp]").each(function(){
      var timestamp = $(this).data("timestamp");
      var local_time = moment.tz(timestamp, "X", moment.tz.guess());
      $(this).text(local_time.format("HH:mm z, LL"));
    });
    </script>
    ';
}
?>
<?php include('../includes/footer.php'); ?>
