<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions</title>
    <link href="https://fonts.googleapis.com/css2?family=Crimson+Text&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/output.css">
</head>

<body>

    <?php require_once __DIR__ . '/../components/header.view.php'; ?>

    <div class="py-10 px-30 flex flex-col gap-5">
        <div class="justify-start text-black text-lg font-normal font-crimson">
            <a href="/home" class="hover:underline">Home</a> <a href="/signup" class="hover:underline">&gt;
                Authentication</a> &gt; Terms & Conditions
        </div>

        <h1 class="font-crimson font-bold text-3xl">TERMS & CONDITIONS</h1>

        <div class="mx-auto h-1 w-full bg-yellow-900/60 rounded-lg"></div>

        <p class="mt-4 text-gray-700 font-poppins">
            By using this website, you agree to the following terms and conditions. Please read them carefully.
        </p>

        <h2 class="font-bold mt-4">1. Use of Website</h2>
        <p class="text-gray-700 font-poppins">
            You may use this website for lawful purposes only. You must not use it in any way that could damage or
            impair the site or its services.
        </p>

        <h2 class="font-bold mt-4">2. Account Registration</h2>
        <p class="text-gray-700 font-poppins">
            Users must provide accurate information when creating an account. You are responsible for maintaining the
            confidentiality of your login details.
        </p>

        <h2 class="font-bold mt-4">3. Intellectual Property</h2>
        <p class="text-gray-700 font-poppins">
            All content on this website, including text, images, and logos, is the property of the site and protected by
            copyright laws.
        </p>

        <h2 class="font-bold mt-4">4. Limitation of Liability</h2>
        <p class="text-gray-700 font-poppins">
            We are not liable for any damages arising from your use of the website, including loss of data or business
            interruption.
        </p>

        <h2 class="font-bold mt-4">5. Changes to Terms</h2>
        <p class="text-gray-700 font-poppins">
            We may update these Terms & Conditions at any time. Your continued use of the website indicates acceptance
            of the new terms.
        </p>

        <p class="mt-4 text-gray-700 font-poppins">
            For questions, contact us at <a href="mailto:subic.rivera@hotelsubic.com"
                class="underline hover:text-gray-800">support@example.com</a>.
        </p>

        <a href="/signup">
            <button class="rounded-sm hover:bg-[#3F321F] shadow-2xl py-2 px-4 bg-[#C39C4D] transition-colors">
                <p
                    class="transition-all duration-300 text-white hover:text-white hover:[text-shadow:0_0_8px_rgba(255,255,255,0.9)]">
                    BACK
                </p>
            </button>
        </a>

    </div>

</body>

</html>