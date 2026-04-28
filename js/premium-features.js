// premium-features.js — Optimized for fast page load

// === 5. Global Sticky Header Shrink (runs immediately, not deferred) ===
(function() {
    const mainHeader = document.querySelector('.header');
    if(mainHeader) {
        window.addEventListener('scroll', () => {
            // Don't shrink while a mega-menu is open
            if (mainHeader.classList.contains('mega-open')) return;
            if (window.scrollY > 80) {
                mainHeader.classList.add('shrink');
            } else if (window.scrollY < 20) {
                mainHeader.classList.remove('shrink');
            }
        }, { passive: true });
    }
})();

// === MEGA-MENU: JavaScript-controlled hover with debounce (anti-flicker) ===
(function() {
    const dropdowns = document.querySelectorAll('.has-dropdown');
    const header = document.querySelector('.header');
    if (!dropdowns.length) return;

    let closeTimer = null;
    let currentOpen = null;

    function openMenu(li) {
        if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        if (currentOpen && currentOpen !== li) {
            currentOpen.classList.remove('mega-hover');
        }
        li.classList.add('mega-hover');
        currentOpen = li;
        if (header) header.classList.add('mega-open');
    }

    function scheduleClose(li) {
        closeTimer = setTimeout(() => {
            li.classList.remove('mega-hover');
            if (currentOpen === li) currentOpen = null;
            if (header) header.classList.remove('mega-open');
            closeTimer = null;
        }, 200);
    }

    dropdowns.forEach(li => {
        li.addEventListener('mouseenter', () => openMenu(li));
        li.addEventListener('mouseleave', () => scheduleClose(li));
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    // === 3. Global Dark Mode Toggle Logic (critical, runs first) ===
    const themeToggle = document.getElementById('theme-toggle');
    if(themeToggle) {
        const themeIconMoon = document.getElementById('theme-icon');
        const themeIconSun = document.getElementById('theme-icon-sun');
        const htmlEl = document.documentElement;

        const updateIcons = (theme) => {
            if (themeIconMoon && themeIconSun) {
                if (theme === 'dark') {
                    themeIconMoon.style.display = 'none';
                    themeIconSun.style.display = 'inline-block';
                } else {
                    themeIconMoon.style.display = 'inline-block';
                    themeIconSun.style.display = 'none';
                }
            } else {
                themeToggle.innerHTML = theme === 'dark' ? '<i class=\'ri-sun-fill\'></i>' : '<i class=\'ri-moon-line\'></i>';
            }
        };

        const initTheme = localStorage.getItem('theme') || 'light';
        if (initTheme === 'dark') {
            htmlEl.setAttribute('data-theme', 'dark');
        } else {
            htmlEl.removeAttribute('data-theme');
        }
        updateIcons(initTheme);

        themeToggle.addEventListener('click', () => {
            const isDark = htmlEl.getAttribute('data-theme') === 'dark';
            const newTheme = isDark ? 'light' : 'dark';
            
            if (newTheme === 'dark') {
                htmlEl.setAttribute('data-theme', 'dark');
            } else {
                htmlEl.removeAttribute('data-theme');
            }
            
            localStorage.setItem('theme', newTheme);
            updateIcons(newTheme);
        });
    }

    // === 2. Bookmarks Logic ===
    const bookmarkBadge = document.getElementById('bookmark-badge');
    let bookmarks = JSON.parse(localStorage.getItem('htv_bookmarks')) || [];
    
    function updateBadge() {
        if(bookmarkBadge) {
            bookmarkBadge.textContent = bookmarks.length;
            bookmarkBadge.style.display = bookmarks.length > 0 ? 'flex' : 'none';
        }
    }
    updateBadge();

    const btnSave = document.getElementById('btn-save-bookmark');
    if(btnSave) {
        const slug = btnSave.getAttribute('data-slug');
        const isSaved = bookmarks.find(b => b.slug === slug);
        
        if(isSaved) {
            btnSave.innerHTML = '<i class="ri-bookmark-fill" style="color:var(--danger);"></i> Guardado';
            btnSave.style.borderColor = 'var(--danger)';
        }

        btnSave.addEventListener('click', (e) => {
            e.preventDefault();
            const exists = bookmarks.findIndex(b => b.slug === slug);
            if(exists >= 0) {
                bookmarks.splice(exists, 1);
                btnSave.innerHTML = '<i class="ri-bookmark-line"></i> Guardar Nota';
                btnSave.style.borderColor = 'var(--border-color)';
            } else {
                bookmarks.push({
                    slug: slug,
                    title: btnSave.getAttribute('data-title'),
                    img: btnSave.getAttribute('data-img'),
                    date: new Date().toISOString()
                });
                btnSave.innerHTML = '<i class="ri-bookmark-fill" style="color:var(--danger);"></i> Guardado';
                btnSave.style.borderColor = 'var(--danger)';
            }
            localStorage.setItem('htv_bookmarks', JSON.stringify(bookmarks));
            updateBadge();
        });
    }

    // === 4. Global Live Search Logic ===
    const liveSearchInput = document.getElementById('live-search-input');
    const liveSearchResults = document.getElementById('live-search-results');
    let searchTimeout = null;

    if(liveSearchInput && liveSearchResults) {
        liveSearchInput.addEventListener('input', function(e) {
            const q = e.target.value.trim();
            clearTimeout(searchTimeout);
            if(q.length < 2) {
                liveSearchResults.style.display = 'none';
                return;
            }
            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`api/search?q=${encodeURIComponent(q)}`);
                    if(res.ok) {
                        const data = await res.json();
                        if(data.length > 0) {
                            let html = '';
                            data.forEach(item => {
                                html += `<a href="article.php?slug=${item.slug}" style="display:flex; gap:10px; padding:10px; border-bottom:1px solid var(--border-color); text-decoration:none; align-items:flex-start;" class="hover-title-primary">
                                            <img src="${item.imagen_url ? item.imagen_url : 'img/logo.webp'}" style="width:50px; height:50px; min-width:50px; flex-shrink:0; object-fit:cover; border-radius:4px;" onerror="this.src='img/logo.webp'">
                                            <span style="font-size:0.85rem; color:var(--text-main); font-weight:600; line-height:1.3; overflow:hidden; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3;">${item.titulo}</span>
                                         </a>`;
                            });
                            html += `<a href="search.php?q=${encodeURIComponent(q)}" style="display:block; text-align:center; padding:10px; font-size:0.8rem; color:var(--primary-color); font-weight:800; background:rgba(0,0,0,0.02);">VER TODOS LOS RESULTADOS</a>`;
                            liveSearchResults.innerHTML = html;
                            liveSearchResults.style.display = 'block';
                        } else {
                            liveSearchResults.innerHTML = `<div style="padding:15px; text-align:center; font-size:0.85rem; color:var(--text-muted);">No se encontraron noticias.</div>`;
                            liveSearchResults.style.display = 'block';
                        }
                    }
                } catch(err) {
                    console.error("Live search failed", err);
                }
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            const container = document.getElementById('live-search-container');
            if(container && !container.contains(e.target)) {
                liveSearchResults.style.display = 'none';
            }
        });
    }

    // === 1. Weather API — DEFERRED & CACHED (non-critical) ===
    const tempSpan = document.getElementById('weather-temp');
    const iconSpan = document.getElementById('weather-icon');
    
    if (tempSpan) {
        // Check cache first (5 min TTL)
        const cached = localStorage.getItem('htv_weather');
        if (cached) {
            try {
                const w = JSON.parse(cached);
                if (Date.now() - w.ts < 300000) { // 5 min
                    tempSpan.innerHTML = `<span style="font-weight:600; margin-right:4px;">${w.city}</span>${w.temp}°C`;
                    if (iconSpan) {
                        iconSpan.className = w.icon;
                        iconSpan.style.color = w.iconColor;
                    }
                    return; // Use cache, skip fetch
                }
            } catch(e) {}
        }

        // Use requestIdleCallback or setTimeout fallback to defer weather fetch
        const deferFn = window.requestIdleCallback || function(cb) { setTimeout(cb, 1500); };
        deferFn(function() {
            fetch('https://get.geojs.io/v1/ip/geo.json')
                .then(res => res.json())
                .then(geo => {
                    const lat = geo.latitude || -5.1945;
                    const lon = geo.longitude || -80.6328;
                    const city = geo.city || 'Piura';
                    
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
                        .then(res => res.json())
                        .then(data => {
                            if(data.current_weather) {
                                const temp = Math.round(data.current_weather.temperature);
                                tempSpan.innerHTML = `<span style="font-weight:600; margin-right:4px;">${city}</span>${temp}°C`;
                                
                                let icon, iconColor;
                                if(data.current_weather.is_day) {
                                    icon = temp > 25 ? 'ri-sun-fill' : 'ri-sun-cloudy-fill';
                                    iconColor = '#fbbf24';
                                } else {
                                    icon = 'ri-moon-fill';
                                    iconColor = '#94a3b8';
                                }
                                if (iconSpan) {
                                    iconSpan.className = icon;
                                    iconSpan.style.color = iconColor;
                                }
                                // Cache result
                                localStorage.setItem('htv_weather', JSON.stringify({
                                    city, temp, icon, iconColor, ts: Date.now()
                                }));
                            }
                        }).catch(e => {
                            tempSpan.textContent = '--';
                            if (iconSpan) iconSpan.className = 'ri-cloud-off-line';
                        });
                })
                .catch(e => {
                    // Fallback a Piura si falla la geolocalización
                    fetch('https://api.open-meteo.com/v1/forecast?latitude=-5.1945&longitude=-80.6328&current_weather=true')
                        .then(res => res.json())
                        .then(data => {
                            if(data.current_weather) {
                                const temp = Math.round(data.current_weather.temperature);
                                tempSpan.innerHTML = `<span style="font-weight:600; margin-right:4px;">Piura</span>${temp}°C`;
                                let icon = 'ri-sun-fill', iconColor = '#fbbf24';
                                if(data.current_weather.is_day) {
                                    icon = temp > 25 ? 'ri-sun-fill' : 'ri-sun-cloudy-fill';
                                } else {
                                    icon = 'ri-moon-fill';
                                    iconColor = '#94a3b8';
                                }
                                if (iconSpan) {
                                    iconSpan.className = icon;
                                    iconSpan.style.color = iconColor;
                                }
                                localStorage.setItem('htv_weather', JSON.stringify({
                                    city: 'Piura', temp, icon, iconColor, ts: Date.now()
                                }));
                            }
                        });
                });
        });
    }
});

