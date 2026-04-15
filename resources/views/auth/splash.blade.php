<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Sisirin'e</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>
html,body{
height:100%;
margin:0;
overflow:hidden;
background:#0f172a;
}

.fade-in{
animation:fadeIn 1.2s ease-out forwards;
}

@keyframes fadeIn{
from{opacity:0;transform:scale(.95)}
to{opacity:1;transform:scale(1)}
}
</style>

</head>
<body class="antialiased">

<div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-[#0f172a] via-[#161e35] to-[#0f172a]">

<div class="w-full max-w-[320px] px-8 text-center fade-in">

<div class="flex justify-center mb-10">
<img 
src="{{ asset('image/logo1.png') }}" 
alt="Sisirin'e Logo"
class="w-full h-auto drop-shadow-xl">
</div>

<div class="flex flex-col items-center">

<div class="w-10 h-10 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin"></div>

<p class="text-blue-200/50 text-[11px] mt-6 uppercase tracking-[0.4em] font-medium">
Menyiapkan Sistem
</p>

</div>

</div>

</div>

<script>

setTimeout(() => {

    // redirect mengikuti domain yang sedang dibuka
    const loginUrl = window.location.origin + "/login";

    // redirect tanpa menyimpan splash di history
    window.location.replace(loginUrl);

},3000);

</script>

</body>
</html>