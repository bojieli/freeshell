<?php
$supported_distributions = array(
    "Debian 7.0 wheezy (Recommended)" => "debian-7.0-amd64-minimal",
    "Debian 6.0 squeeze" => "debian-6.0-amd64-minimal",
    "CentOS 6" => "centos-6-x86_64",
    "CentOS 5" => "centos-5-x86_64",
    "Ubuntu 12.04 LTS" => "ubuntu-12.04-x86_64",
    "Ubuntu 13.10" => "ubuntu-13.10-x86_64",
    "Fedora 20" => "fedora-20-x86_64",
    "OpenSUSE 13.1" => "suse-13.1-x86_64",
    "OpenSUSE 12.3" => "suse-12.3-x86_64",
    "Archlinux" => "arch-20131014-x86_64",
);

function distribution_option_html($default = "debian-7.0-amd64-minimal") {
    global $supported_distributions;
    $str = "";
    foreach ($supported_distributions as $nickname => $name) {
        $selected = ($default == $name) ? ' selected="selected"' : '';
        $str .= "<option value=\"$name\"$selected>$nickname</option>";
    }
    return $str;
}
