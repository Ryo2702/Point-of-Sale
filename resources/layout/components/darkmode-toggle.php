 <!-- Theme Toggle -->
 <div class="mb-6">
     <div class="form-control w-52">
         <label class="cursor-pointer label">
             <span class="label-text">Dark mode</span>
             <input type="checkbox" class="toggle toggle-primary" id="themeToggle" />
         </label>
     </div>
 </div>

 <script>
     // Theme toggle functionality
     const themeToggle = document.getElementById('themeToggle');
     const html = document.documentElement;

     themeToggle.addEventListener('change', function() {
         if (this.checked) {
             html.setAttribute('data-theme', 'dark');
         } else {
             html.setAttribute('data-theme', 'light');
         }
     });
 </script>