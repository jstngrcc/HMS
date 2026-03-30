<?php
$logged_in = $logged_in ?? false;
?>
<nav class="w-full bg-linear-to-r from-black/75 to-stone-500/75 flex justify-between gap-10 px-25">
    <a href="/home">
        <img src="/assets/images/logo.jpg" alt="logo" class="h-full w-32 object-cover">
    </a>
    <div class="flex justify-between gap-5 items-center">
        <?php require_once __DIR__ . '/profile.view.php'; ?>
        <a href="/cart" class="relative inline-block">
            <img src="/assets/icons/cart.svg" alt="Cart icon" class="h-8 w-8 transition-all duration-200 rounded 
        hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.9)] hover:scale-105">

            <?php if (!empty($cartCount) && $cartCount > 0): ?>
                <span class="absolute top-0 right-0 translate-x-1/2 -translate-y-1/3 
                     bg-[#FCEDB5] text-black font-crimson text-xs font-bold 
                     w-4 h-4 flex items-center justify-center 
                     border border-[#FCEDB5]">
                    <?= $cartCount ?>
                </span>
            <?php endif; ?>
        </a>

    </div>
</nav>