<?php
/**
 * Header.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        night: '#07140f',
                        moss: '#0f2a1f',
                        sage: '#1f4d3a',
                        gold: '#c9a227',
                        sand: '#e8dcc4',
                        mist: '#9fb8a8'
                    },
                    fontFamily: {
                        display: ['Amiri', 'serif'],
                        sans: ['Manrope', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(201, 162, 39, 0.18), transparent 55%),
                radial-gradient(ellipse 60% 40% at 90% 20%, rgba(31, 77, 58, 0.45), transparent 50%),
                linear-gradient(180deg, #07140f 0%, #0a1c14 45%, #06110d 100%);
            min-height: 100vh;
        }
        .starfield::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                radial-gradient(1px 1px at 12% 18%, rgba(232, 220, 196, 0.55), transparent),
                radial-gradient(1px 1px at 28% 42%, rgba(232, 220, 196, 0.35), transparent),
                radial-gradient(1.5px 1.5px at 67% 22%, rgba(201, 162, 39, 0.45), transparent),
                radial-gradient(1px 1px at 81% 58%, rgba(232, 220, 196, 0.3), transparent),
                radial-gradient(1px 1px at 45% 72%, rgba(232, 220, 196, 0.4), transparent);
            opacity: 0.7;
            z-index: 0;
        }
        .fade-in { animation: fadeUp 0.55s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .row-qadr { background: linear-gradient(90deg, rgba(201,162,39,0.12), transparent); }
        .row-today { outline: 1px solid rgba(201,162,39,0.45); outline-offset: -1px; }
    </style>
</head>
<body <?php body_class('starfield text-sand font-sans antialiased'); ?>>
<?php wp_body_open(); ?>
