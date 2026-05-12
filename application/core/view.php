<?php
class View {
    function generate($content_view) {
        include 'application/views/' . $content_view;
    }
}
?>