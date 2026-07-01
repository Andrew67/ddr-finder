<?php
// Redirect to DDR Finder home page if someone happens to hit this subdomain directly.
http_response_code(302);
header('Location: https://ddrfinder.andrew67.com/');
