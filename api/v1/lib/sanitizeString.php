<?php
function filterThis($string) {
    return htmlspecialchars(strip_tags($string));
}