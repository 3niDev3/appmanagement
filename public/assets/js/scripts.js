(function () {
    "use strict";

    /** Add .scrolled on scroll */
    function toggleScrolled() {
        const body = document.body;
        const header = document.querySelector('#header');
        if (!header.classList.contains('scroll-up-sticky') && !header.classList.contains('sticky-top') && !header.classList.contains('fixed-top')) return;
        window.scrollY > 100 ? body.classList.add('scrolled') : body.classList.remove('scrolled');
    }
    document.addEventListener('scroll', toggleScrolled);
    window.addEventListener('load', toggleScrolled);

    /** Mobile nav toggle */
    const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');
    if (mobileNavToggleBtn) {
        mobileNavToggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('mobile-nav-active');
            mobileNavToggleBtn.classList.toggle('bi-list');
            mobileNavToggleBtn.classList.toggle('bi-x');
        });
    }

    /** Close mobile nav on nav link click, but NOT on dropdown toggles */
    document.querySelectorAll('#navmenu a').forEach(link => {
        link.addEventListener('click', (e) => {
            const parentDropdown = link.closest('.dropdown');
            const hasSubmenu = parentDropdown && parentDropdown.querySelector('.dropdown-menu');

            if (!hasSubmenu && document.body.classList.contains('mobile-nav-active')) {
                document.body.classList.remove('mobile-nav-active');
                mobileNavToggleBtn.classList.toggle('bi-list');
                mobileNavToggleBtn.classList.toggle('bi-x');
            }
        });
    });


    /** Mobile nav dropdown toggle (custom) */
    document.querySelectorAll('.navmenu .toggle-dropdown').forEach(toggle => {
        toggle.addEventListener('click', e => {
            e.preventDefault();
            toggle.parentNode.classList.toggle('active');
            toggle.parentNode.nextElementSibling.classList.toggle('dropdown-active');
        });
    });

    /** FAQ accordion */
    document.querySelectorAll(".question").forEach(question => {
        question.addEventListener("click", () => {
            const active = document.querySelector(".question.active");
            if (active && active !== question) {
                active.classList.remove("active");
                active.nextElementSibling.style.maxHeight = 0;
            }
            question.classList.toggle("active");
            const answer = question.nextElementSibling;
            answer.style.maxHeight = question.classList.contains("active") ? answer.scrollHeight + "px" : 0;
        });
    });

    /** Desktop hover submenu */
    /** Desktop hover submenu */
    const itemsWithSubmenu = document.querySelectorAll('.item.has-submenu');
    itemsWithSubmenu.forEach(item => {
        const submenuId = item.getAttribute('data-submenu');
        const submenu = document.getElementById(submenuId);
        if (submenu) {
            // Desktop hover - only for >= 1200px
            item.addEventListener('mouseenter', () => {
                if (window.innerWidth >= 1200) {
                    document.querySelectorAll('.submenu').forEach(s => s.classList.remove('active'));
                    submenu.classList.add('active');
                }
            });
            item.addEventListener('mouseleave', () => {
                if (window.innerWidth >= 1200) {
                    setTimeout(() => {
                        if (!submenu.matches(':hover')) submenu.classList.remove('active');
                    }, 100);
                }
            });
            submenu.addEventListener('mouseleave', () => {
                if (window.innerWidth >= 1200) {
                    submenu.classList.remove('active');
                }
            });
        }
    });

    /** Mobile submenu click handling */
    document.querySelectorAll('.item.has-submenu').forEach(trigger => {
        trigger.addEventListener('click', e => {
            if (window.innerWidth <= 1199) {
                e.preventDefault();
                const targetId = trigger.getAttribute('data-submenu');
                const submenu = document.getElementById(targetId);

                if (submenu) {
                    const isCurrentlyActive = submenu.classList.contains('active');
                    
                    // First close all submenus and move them back
                    document.querySelectorAll('.right-column.submenu.active').forEach(s => {
                        s.classList.remove('active');
                        if (s.parentNode && s.parentNode.classList.contains('moved')) {
                            // Move back to dropdown-columns
                            const dropdownColumns = document.querySelector('.dropdown-columns');
                            if (dropdownColumns) {
                                dropdownColumns.appendChild(s);
                                s.parentNode.classList.remove('moved');
                            }
                        }
                    });

                    // If it wasn't active before, open it
                    if (!isCurrentlyActive) {
                        submenu.classList.add('active');
                        // Move submenu under clicked item
                        trigger.after(submenu);
                        if (trigger.parentNode) {
                            trigger.parentNode.classList.add('moved');
                        }
                    }
                }
            }
        });
    });

    /** Navmenu Scrollspy */
    const navmenulinks = document.querySelectorAll('.navmenu a');
    function navmenuScrollspy() {
        const pos = window.scrollY + 200;
        navmenulinks.forEach(link => {
            if (!link.hash) return;
            const section = document.querySelector(link.hash);
            if (section) {
                if (pos >= section.offsetTop && pos <= section.offsetTop + section.offsetHeight) {
                    document.querySelectorAll('.navmenu a.active').forEach(l => l.classList.remove('active'));
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    window.addEventListener('load', navmenuScrollspy);
    document.addEventListener('scroll', navmenuScrollspy);

    /** Navmenu dropdown toggle on mobile (<1200) */
    document.querySelectorAll('.navmenu .dropdown > a').forEach(link => {
        link.addEventListener('click', e => {
            if (window.innerWidth < 1200) {
                e.preventDefault();

                const parent = link.parentElement;
                const submenuId = parent.getAttribute('data-submenu'); // e.g. data-submenu="about-submenu"
                const submenu = submenuId ? document.getElementById(submenuId) : null;
                const isOpen = parent.classList.contains('open');

                // Close all open dropdowns and submenus
                document.querySelectorAll('.navmenu .dropdown.open').forEach(d => {
                    d.classList.remove('open');
                    const menu = d.querySelector('.dropdown-menu');
                    if (menu) menu.style.display = 'none';
                });
                document.querySelectorAll('.right-column.submenu.active').forEach(s => s.classList.remove('active'));

                if (!isOpen) {
                    parent.classList.add('open');
                    const menu = parent.querySelector('.dropdown-menu');
                    if (menu) menu.style.display = 'block';
                    if (submenu) submenu.classList.add('active');
                }
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1200) {
            // Remove all mobile-only classes
            document.querySelectorAll('.navmenu .dropdown.open').forEach(d => {
                d.classList.remove('open');
                const menu = d.querySelector('.dropdown-menu');
                if (menu) menu.style.display = '';
            });
            document.querySelectorAll('.right-column.submenu.active').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.item.has-submenu.active').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.navmenu .dropdown.active').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.dropdown-menu').forEach(menu => menu.style.display = '');
        }
    });

   

    /** Animation on scroll init */
    window.addEventListener('load', () => {
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    });

    /** Fix scroll on load with hash */
    window.addEventListener('load', () => {
        if (window.location.hash) {
            const section = document.querySelector(window.location.hash);
            if (section) {
                setTimeout(() => {
                    const scrollMargin = parseInt(getComputedStyle(section).scrollMarginTop) || 0;
                    window.scrollTo({ top: section.offsetTop - scrollMargin, behavior: 'smooth' });
                }, 100);
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-button');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(tab.getAttribute('data-tab')).classList.add('active');
            });
        });
    });         

    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.pipze-spread-tab');
        const panes = document.querySelectorAll('.pipze-tab-pane');

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));
                tab.classList.add('active');
                panes[index].classList.add('active');
            });
        });
    });

    // Tabs
    document.querySelectorAll('.pipze-spread-tab').forEach(tab => {
        tab.addEventListener('click', () => {
        document.querySelectorAll('.pipze-spread-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        // TODO: add logic to load table data per tab if needed
        });
    });

    // Filters
    document.querySelectorAll('.pipze-spread-filter').forEach(filter => {
        filter.addEventListener('click', () => {
        document.querySelectorAll('.pipze-spread-filter').forEach(f => f.classList.remove('active'));
        filter.classList.add('active');
        // TODO: add logic to filter table data per filter if needed
        });
    });
})();
  
function moveStepCircle(step) {
    const stepIndex = parseInt(step) - 1;
    const stepCircle = document.querySelector(".step-circle");
    const stepLine = document.querySelector(".step-line");
    const totalSteps = document.querySelectorAll(".step-section").length;

    if (!stepCircle || !stepLine) return;

    const lineWidth = stepLine.offsetWidth;
    const spacing = totalSteps > 1 ? lineWidth / (totalSteps - 1) : 0;
    const newLeft = spacing * stepIndex;
    stepCircle.style.left = `${newLeft}px`;
}

class HorizontalScrollController {
    constructor() {
        this.currentStep = 0;
        this.isScrolling = false;
        this.scrollTimeout = null;
        this.stepsContainer = document.querySelector('.step-content');
        this.stepSections = Array.from(document.querySelectorAll('.step-section'));
        this.totalSteps = this.stepSections.length;
        this.isMobile = window.innerWidth <= 768;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.init();
    }

    init() {
        this.addEventListeners();
        this.updateStep(true);
        this.handleResize();
    }

    addEventListeners() {
        if (!this.stepsContainer) {
            console.warn("Warning: .step-content not found. Skipping scroll controller setup.");
            return;
        }

        // Desktop wheel events
        this.stepsContainer.addEventListener('wheel', (e) => {
            if (!this.isMobile) {
                e.preventDefault();
                if (e.deltaX !== 0) {
                    this.handleScroll(e.deltaX > 0 ? 'right' : 'left');
                } else if (e.deltaY !== 0) {
                    this.handleScroll(e.deltaY > 0 ? 'down' : 'up');
                }
            }
        });

        // Mobile touch events - IMPROVED
        this.stepsContainer.addEventListener('touchstart', (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
        }, { passive: true });

        this.stepsContainer.addEventListener('touchmove', (e) => {
            if (this.isMobile) {
                const touchCurrentX = e.touches[0].clientX;
                const touchCurrentY = e.touches[0].clientY;
                const diffX = Math.abs(touchCurrentX - this.touchStartX);
                const diffY = Math.abs(touchCurrentY - this.touchStartY);
                
                // Only prevent default if horizontal swipe is more significant than vertical
                // AND the event is cancelable
                if (diffX > diffY && diffX > 30 && e.cancelable) {
                    e.preventDefault();
                }
            }
        }, { passive: false });

        this.stepsContainer.addEventListener('touchmove', (e) => {
            if (this.isMobile && e.cancelable) {
                const touchCurrentX = e.touches[0].clientX;
                const touchCurrentY = e.touches[0].clientY;
                const diffX = Math.abs(touchCurrentX - this.touchStartX);
                const diffY = Math.abs(touchCurrentY - this.touchStartY);
                
                // More aggressive early detection
                if (diffX > 10 && diffX > diffY * 1.5) {
                    e.preventDefault();
                }
            }
        }, { passive: false });

        this.stepsContainer.addEventListener('touchend', (e) => {
            if (this.isMobile) {
                const touchEndX = e.changedTouches[0].clientX;
                const touchEndY = e.changedTouches[0].clientY;
                const diffX = this.touchStartX - touchEndX;
                const diffY = Math.abs(this.touchStartY - touchEndY);

                // Only handle horizontal swipe if it's more significant than vertical movement
                if (Math.abs(diffX) > 50 && Math.abs(diffX) > diffY) {
                    this.handleScroll(diffX > 0 ? 'right' : 'left');
                }
            }
        }, { passive: true });

        // Keyboard events - only for desktop
        document.addEventListener('keydown', (e) => {
            if (!this.isMobile && this.isInStepsSection()) {
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.handleScroll('right');
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.handleScroll('left');
                }
            }
        });

        // Scroll events - with throttling
        let scrollTimer = null;
        this.stepsContainer.addEventListener('scroll', () => {
            if (scrollTimer) clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                this.updateCurrentStepFromScroll();
            }, 100);
        });

        // Resize handler
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }

    handleResize() {
        this.isMobile = window.innerWidth <= 768;
        
        // Recalculate positions on resize
        setTimeout(() => {
            this.scrollToCurrentStep();
            moveStepCircle(this.currentStep + 1);
        }, 100);
    }

    handleScroll(direction) {
        if (this.isScrolling) return;

        this.isScrolling = true;
        let nextStep = this.currentStep;

        if (direction === 'right' || direction === 'down') {
            nextStep = Math.min(this.totalSteps - 1, this.currentStep + 1);
        } else if (direction === 'left' || direction === 'up') {
            nextStep = Math.max(0, this.currentStep - 1);
        }

        if (nextStep !== this.currentStep) {
            this.currentStep = nextStep;
            this.scrollToCurrentStep();
            moveStepCircle(this.currentStep + 1);
        } else {
            // Handle scrolling out of section
            this.handleSectionBoundary(direction);
        }

        clearTimeout(this.scrollTimeout);
        this.scrollTimeout = setTimeout(() => {
            this.isScrolling = false;
        }, this.isMobile ? 600 : 800);
    }

    handleSectionBoundary(direction) {
        if (this.isMobile) return; // Don't auto-scroll sections on mobile
        
        const parentSection = this.stepsContainer.closest('section');
        if (parentSection) {
            if ((direction === 'right' || direction === 'down') && this.currentStep === this.totalSteps - 1) {
                let nextGlobalSection = parentSection.nextElementSibling;
                while (nextGlobalSection && nextGlobalSection.offsetHeight === 0) {
                    nextGlobalSection = nextGlobalSection.nextElementSibling;
                }
                if (nextGlobalSection) {
                    nextGlobalSection.scrollIntoView({ behavior: 'smooth' });
                }
            } else if ((direction === 'left' || direction === 'up') && this.currentStep === 0) {
                let prevGlobalSection = parentSection.previousElementSibling;
                while (prevGlobalSection && prevGlobalSection.offsetHeight === 0) {
                    prevGlobalSection = prevGlobalSection.previousElementSibling;
                }
                if (prevGlobalSection) {
                    prevGlobalSection.scrollIntoView({ behavior: 'smooth' });
                }
            }
        }
    }

    scrollToCurrentStep() {
        const containerWidth = this.stepsContainer.offsetWidth;
        const targetScrollLeft = this.currentStep * containerWidth;
        
        this.stepsContainer.scrollTo({
            left: targetScrollLeft,
            behavior: 'smooth'
        });
    }

    updateStep(initial = false) {
        this.stepSections.forEach((section, index) => {
            if (index === this.currentStep) {
                section.classList.add('active');
            } else {
                section.classList.remove('active');
            }
        });
        
        if (!initial) {
            moveStepCircle(this.currentStep + 1);
        }
    }

    updateCurrentStepFromScroll() {
        const scrollLeft = this.stepsContainer.scrollLeft;
        const containerWidth = this.stepsContainer.offsetWidth;
        const newStep = Math.round(scrollLeft / containerWidth);

        if (newStep !== this.currentStep && newStep >= 0 && newStep < this.totalSteps) {
            this.currentStep = newStep;
            this.updateStep();
        }
    }

    isInStepsSection() {
        if (!this.stepsContainer) return false;
        const stepsContainer = this.stepsContainer.closest('.steps-container');
        if (!stepsContainer) return false;
        
        const rect = stepsContainer.getBoundingClientRect();
        return rect.top < window.innerHeight && rect.bottom >= 0;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new HorizontalScrollController();
});

// document.addEventListener('scroll', () => {
//     const scrolled = window.pageYOffset;
//     const parallax = document.querySelector('.intro-section');
//     if (parallax) {
//         const speed = scrolled * 0.5;
//         parallax.style.transform = `translateY(${speed}px)`;
//     }
// });
//     const categoryData = {
//     forex: [
//         { symbol: "EUR/USD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/USD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "AUD/USD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "NZD/USD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "USD/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "USD/CAD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "USD/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "AUD/CAD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "AUD/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "AUD/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "AUD/NZD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "CAD/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "CHF/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/CAD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/NZD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "CAD/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "NZD/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/NZD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/AUD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/GBP", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/CHF", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "GBP/JPY", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/CAD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EUR/AUD", size: "100000", spread: "0", commission: "5", min: "0.01", max: "100", leverage: "1:500", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },

//     ],
//     commodities: [
//         { symbol: "XAU/USD", size: "1", spread: "0.9", commission: "5", min: "0.01", max: "20", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "XAG/USD", size: "5000", spread: "1.5", commission: "5", min: "0.01", max: "15", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//     ],
//     indices: [
//         { symbol: "AU200", size: "1", spread: "1.2", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "D40", size: "1", spread: "2.0", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "ES35", size: "1", spread: "5.0", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "EU50", size: "1", spread: "1.1", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "FR40", size: "1", spread: "0.9", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "HK50", size: "1", spread: "4.0", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "UK100", size: "1", spread: "0.7", commission: "5", min: "0.01", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "JP225", size: "1", spread: "5.0", commission: "5", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "US30", size: "1", spread: "10.0", commission: "5", min: "0.1", max: "1000", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "US500", size: "1", spread: "3.0", commission: "5", min: "0.1", max: "50", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//         { symbol: "UT100", size: "1", spread: "0.7", commission: "5", min: "0.1", max: "50", leverage: "1:500", hours: "Mon–Fri 01:00–23:15" },
//     ],
//     energy: [
//         { symbol: "CRUDE", size: "1000", spread: "1.0", commission: "5 USD", min: "0.01", max: "40", leverage: "1:100", hours: "Mon–Fri 03:00–24:00" },
//         { symbol: "BREBRENTNT", size: "1000", spread: "1.0", commission: "5 USD", min: "0.01", max: "20", leverage: "1:100", hours: "Mon–Fri 03:00–24:00" },
//         { symbol: "NATGAS", size: "10000", spread: "1.3", commission: "5 USD", min: "0.01", max: "20", leverage: "1:100", hours: "Mon–Fri 03:00–24:00" },
//     ],
//     crypto: [
//         { symbol: "BTC/USD", size: "1", spread: "1700", commission: "5 USD", min: "0.01", max: "10", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "ETH/USD", size: "1", spread: "10", commission: "5 USD", min: "0.01", max: "50", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "LTC/USD", size: "1", spread: "0.30", commission: "5 USD", min: "1", max: "20", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "XRP/USD", size: "1", spread: "1.30", commission: "5 USD", min: "10", max: "5000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "BCH/USD", size: "1", spread: "2.10", commission: "5 USD", min: "0.01", max: "100", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "LNK/USD", size: "1", spread: "0.11", commission: "5 USD", min: "1", max: "1000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "ADA/USD", size: "1", spread: "0.11", commission: "5 USD", min: "10", max: "1000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "EOS/USD", size: "1", spread: "0.10", commission: "5 USD", min: "10", max: "1000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "DOG/USD", size: "1", spread: "0.30", commission: "5 USD", min: "10", max: "1000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "DOT/USD", size: "1", spread: "0.30", commission: "5 USD", min: "1", max: "1000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
//         { symbol: "XLM/USD", size: "1", spread: "0.03", commission: "5 USD", min: "100", max: "10000", leverage: "1:5000", hours: "Mon–Thurs 00:01–23:58 Fri 00:01–23:57" },
    
//     ],
//     share: [
//         { symbol: "Amazon", size: "1", spread: "0.5", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Apple", size: "1", spread: "0.5", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Coca", size: "1", spread: "0.3", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Facebook", size: "1", spread: "6.5", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Fedex", size: "1", spread: "1.7", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Ford", size: "1", spread: "0.3", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "GE", size: "1", spread: "1.1", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Hilton", size: "1", spread: "2.0", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "IBM", size: "1", spread: "1.7", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Tesla", size: "1", spread: "0.7", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//         { symbol: "Uber", size: "1", spread: "0.3", commission: "5 USD", min: "1", max: "1000", leverage: "1:500", hours: "Mon–Fri 13:30–20:00" },
//     ]
  
// };
// let currentCategory = "forex";
//   let currentPage = 1;
//   let rowsPerPage = 10;
//   let filteredData = [...categoryData[currentCategory]];

//  function renderTable() {
//   const tableBody = document.getElementById("tableBody");
//   tableBody.innerHTML = "";

//   rowsPerPage = parseInt(document.getElementById("rowsPerPage").value);
//   const start = (currentPage - 1) * rowsPerPage;
//   const end = start + rowsPerPage;
//   const pageData = filteredData.slice(start, end);

//   pageData.forEach(row => {
//     const tr = document.createElement("tr");
//     tr.style.cursor = 'pointer';
//     tr.onclick = () => {
//       const symbolSlug = row.symbol.replace("/", "-").replace(/\s/g, '');
//       window.location.href = `tradingInstrument/symbol/${symbolSlug}`;
//     };
//     tr.innerHTML = `
//       <td>${row.symbol}</td>
//       <td>${row.size}</td>
//       <td>${row.spread}</td>
//       <td>${row.commission}</td>
//       <td>${row.min}</td>
//       <td>${row.max}</td>
//       <td>${row.leverage}</td>
//       <td>${row.hours}</td>
//     `;
//     tableBody.appendChild(tr);
//   });

//   document.getElementById("paginationInfo").innerText = `${start + 1}–${Math.min(end, filteredData.length)} of ${filteredData.length}`;
// }


//   function filterTable() {
//     const query = document.getElementById("searchInput").value.toLowerCase();
//     filteredData = categoryData[currentCategory].filter(row =>
//         row.symbol.toLowerCase().includes(query)
//     );
//     currentPage = 1;
//     renderTable();
//     }


//   function nextPage() {
//     if (currentPage * rowsPerPage < filteredData.length) {
//       currentPage++;
//       renderTable();
//     }
//   }

//   function prevPage() {
//     if (currentPage > 1) {
//       currentPage--;
//       renderTable();
//     }
//   }
//   function changeCategory(el) {
//     const category = el.getAttribute("data-category");
//     currentCategory = category;
//     filteredData = [...categoryData[currentCategory]];
//     currentPage = 1;
//     renderTable();

//     // Optional: highlight active category visually
//     document.querySelectorAll('.category-box').forEach(box => box.classList.remove('active'));
//     el.classList.add('active');

//     // Update title
//     document.querySelector(".instrument-table-title-section h2").textContent = category.charAt(0).toUpperCase() + category.slice(1);
//     }

//   // Initialize
//   renderTable();
