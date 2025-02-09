<template>
    <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="logo-svg">
        <!-- Background shape -->
        <path
            d="M400 250c0 80-120 200-150 200s-150-120-150-200c0-40 30-100 150-100s150 60 150 100z"
            fill="#4A0D2C"
            class="lips-bg"
        />
        <!-- Main lips shape -->
        <path
            d="M380 250c0 60-100 180-130 180s-130-120-130-180c0-35 25-90 130-90s130 55 130 90z"
            fill="url(#outer-gradient)"
            class="lips-outer"
        />
        <!-- Inner lip detail and tongue -->
        <path
            d="M350 250c0 40-85 120-100 120s-100-80-100-120c0-25 20-70 100-70s100 45 100 70z"
            fill="url(#inner-gradient)"
            class="lips-inner"
        />
        <!-- Teeth glimpse -->
        <path
            d="M270 250l15-8 15 8"
            fill="#FFFFFF"
            opacity="0.9"
        />
        <!-- Shine/highlights -->
        <path
            d="M340 220c-20-15-70-30-90-30s-70 15-90 30c15-40 70-50 90-50s75 10 90 50z"
            fill="#FFB6C1"
            opacity="0.2"
            class="lips-highlight"
        />
        <!-- Droplet -->
        <path
            d="M245 370c3 6 7 6 10 0l-5-10z"
            fill="url(#droplet-gradient)"
            class="droplet"
        />
        <!-- Gradients -->
        <defs>
            <linearGradient id="outer-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#FF4D8D" />
                <stop offset="100%" style="stop-color:#FF1493" />
            </linearGradient>
            <linearGradient id="inner-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#FF69B4" />
                <stop offset="50%" style="stop-color:#FF1493" />
                <stop offset="100%" style="stop-color:#C71585" />
            </linearGradient>
            <linearGradient id="droplet-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#FF69B4" />
                <stop offset="100%" style="stop-color:#FFB6C1" />
            </linearGradient>
        </defs>
    </svg>
</template>

<style scoped>
.logo-svg {
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2));
    transition: all 0.3s ease;
}

.logo-svg:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 6px 8px rgba(0, 0, 0, 0.25));
}

.lips-outer,
.lips-inner,
.droplet {
    transition: all 0.3s ease;
}

.logo-svg:hover .lips-outer {
    filter: brightness(1.1);
}

.logo-svg:hover .lips-inner {
    filter: brightness(1.15);
}

.logo-svg:hover .droplet {
    transform: translateY(2px);
}
</style>

<style scoped>
.logo-svg {
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    transition: all 0.3s ease;
}

.logo-svg:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
}

.lips {
    transition: filter 0.3s ease;
}

.logo-svg:hover .lips {
    filter: brightness(1.1);
}
</style>
