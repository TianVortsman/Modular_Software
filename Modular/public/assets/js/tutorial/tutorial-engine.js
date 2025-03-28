// Wrap the class in an IIFE to avoid polluting global scope but expose what we need
(function() {
    class TutorialEngine {
        constructor() {
            this.currentStep = 0;
            this.steps = [];
            this.overlay = null;
            this.tooltip = null;
            this.isActive = false;
        }

        init(pageId) {
            if (!tutorials[pageId]) {
                console.warn(`No tutorial found for page: ${pageId}`);
                return false;
            }

            // Check for multi-page flow
            const savedFlow = localStorage.getItem('tutorial-flow');
            if (savedFlow) {
                const flow = JSON.parse(savedFlow);
                this.currentStep = flow.step;
            } else {
                // Reset to first step when restarting
                this.currentStep = 0;
            }

            this.steps = tutorials[pageId];
            this.createOverlay();
            this.createTooltip();
            this.isActive = true;
            this.showStep();
            return true;
        }

        createOverlay() {
            this.overlay = document.createElement('div');
            this.overlay.className = 'tutorial-overlay';
            document.body.appendChild(this.overlay);
        }

        createTooltip() {
            this.tooltip = document.createElement('div');
            this.tooltip.className = 'tutorial-tooltip';
            document.body.appendChild(this.tooltip);
        }

        async showStep() {
            const step = this.steps[this.currentStep];
            
            // Execute beforeStep function if it exists
            if (step.beforeStep) {
                try {
                    await step.beforeStep();
                } catch (error) {
                    console.warn('Error executing step action:', error);
                }
            }

            // Wait for modal if specified
            if (step.waitForModal && step.modal) {
                await this.waitForModal(step.modal);
            }

            // Show the step content after all async operations
            setTimeout(() => {
                this.showStepContent(step);
            }, 100);
        }

        showStepContent(step) {
            const element = document.querySelector(step.element);
            
            if (!element) {
                console.warn(`Tutorial element not found: ${step.element}, skipping to next step...`);
                if (this.currentStep < this.steps.length - 1) {
                    this.currentStep++;
                    this.showStep();
                } else {
                    this.complete();
                }
                return;
            }

            // Clear previous highlights
            document.querySelectorAll('.tutorial-highlight').forEach(el => {
                el.classList.remove('tutorial-highlight');
            });

            // Add highlight to current element
            element.classList.add('tutorial-highlight');

            // Scroll element into view if not in a modal
            if (!step.modal) {
                const headerOffset = 60;
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }

            // Position tooltip
            this.positionTooltip(element, step);

            // Special handling for employee table
            if (step.element === '#employee-table') {
                this.handleEmployeeTableStep();
            }

            // Update tooltip content
            this.tooltip.innerHTML = `
                <div class="tutorial-header">
                    <h3>${step.title}</h3>
                    <span class="tutorial-counter">${this.currentStep + 1}/${this.steps.length}</span>
                </div>
                <div class="tutorial-content">${step.text}</div>
                <div class="tutorial-controls">
                    ${this.currentStep > 0 ? '<button class="tutorial-back">Back</button>' : ''}
                    <button class="tutorial-skip">Skip</button>
                    <button class="tutorial-next">${this.currentStep === this.steps.length - 1 ? 'Finish' : 'Next'}</button>
                </div>
            `;

            // Add event listeners
            this.addControlListeners();
        }

        positionTooltip(element, step) {
            const elementRect = element.getBoundingClientRect();
            const tooltipRect = this.tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const padding = 20; // Padding from viewport edges

            let top, left;
            let arrowPosition = step.placement;

            // Initial position based on preferred placement
            switch (step.placement) {
                case 'top':
                    top = elementRect.top - tooltipRect.height - padding;
                    left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                    break;
                case 'bottom':
                    top = elementRect.bottom + padding;
                    left = elementRect.left + (elementRect.width - tooltipRect.width) / 2;
                    break;
                case 'left':
                    top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                    left = elementRect.left - tooltipRect.width - padding;
                    break;
                case 'right':
                    top = elementRect.top + (elementRect.height - tooltipRect.height) / 2;
                    left = elementRect.right + padding;
                    break;
            }

            // Adjust horizontal position if tooltip would go outside viewport
            if (left < padding) {
                left = padding;
                if (step.placement === 'top' || step.placement === 'bottom') {
                    arrowPosition = 'left';
                }
            } else if (left + tooltipRect.width > viewportWidth - padding) {
                left = viewportWidth - tooltipRect.width - padding;
                if (step.placement === 'top' || step.placement === 'bottom') {
                    arrowPosition = 'right';
                }
            }

            // Adjust vertical position if tooltip would go outside viewport
            if (top < padding) {
                top = padding;
                if (step.placement === 'left' || step.placement === 'right') {
                    arrowPosition = 'top';
                }
            } else if (top + tooltipRect.height > viewportHeight - padding) {
                top = viewportHeight - tooltipRect.height - padding;
                if (step.placement === 'left' || step.placement === 'right') {
                    arrowPosition = 'bottom';
                }
            }

            // Additional safety check for first step
            if (this.currentStep === 0) {
                // Ensure minimum distance from left edge
                if (left < 20) {
                    left = 20;
                }
                // Ensure minimum distance from right edge
                if (left + tooltipRect.width > viewportWidth - 20) {
                    left = viewportWidth - tooltipRect.width - 20;
                }
            }

            // Apply the position
            this.tooltip.style.top = `${top}px`;
            this.tooltip.style.left = `${left}px`;

            // Update arrow class
            this.tooltip.className = `tutorial-tooltip tutorial-tooltip-${arrowPosition}`;

            // Add data attributes for arrow positioning
            this.tooltip.setAttribute('data-original-placement', step.placement);
            this.tooltip.setAttribute('data-actual-placement', arrowPosition);
        }

        addControlListeners() {
            const backBtn = this.tooltip.querySelector('.tutorial-back');
            const skipBtn = this.tooltip.querySelector('.tutorial-skip');
            const nextBtn = this.tooltip.querySelector('.tutorial-next');

            if (backBtn) {
                backBtn.addEventListener('click', () => this.previousStep());
            }

            skipBtn.addEventListener('click', () => this.end());
            nextBtn.addEventListener('click', () => this.nextStep());
        }

        previousStep() {
            if (this.currentStep > 0) {
                const currentElement = document.querySelector(this.steps[this.currentStep].element);
                if (currentElement) {
                    currentElement.classList.remove('tutorial-highlight');
                }
                this.currentStep--;
                this.showStep();
            }
        }

        nextStep() {
            const currentElement = document.querySelector(this.steps[this.currentStep].element);
            if (currentElement) {
                currentElement.classList.remove('tutorial-highlight');
            }

            if (this.currentStep === this.steps.length - 1) {
                this.complete();
            } else {
                this.currentStep++;
                
                const nextStep = this.steps[this.currentStep];
                if (nextStep.nextPage) {
                    localStorage.setItem('tutorial-flow', JSON.stringify({
                        flow: 'multi-page-flow',
                        step: this.currentStep
                    }));
                    window.location.href = nextStep.nextPage;
                } else {
                    this.showStep();
                }
            }
        }

        complete() {
            const pageId = document.body.id;
            localStorage.setItem(`tutorial-done-${pageId}`, 'true');
            localStorage.removeItem('tutorial-flow');
            this.end();
            
            // Update the tutorial button appearance
            document.body.setAttribute('data-tutorial-completed', 'true');
        }

        end() {
            if (this.overlay) {
                this.overlay.remove();
            }
            if (this.tooltip) {
                this.tooltip.remove();
            }
            
            // Safely remove highlights
            document.querySelectorAll('.tutorial-highlight').forEach(el => {
                el.classList.remove('tutorial-highlight');
            });
            
            // Safely remove tutorial targets
            document.querySelectorAll('.tutorial-target').forEach(el => {
                el.classList.remove('tutorial-target');
                el.style.transition = '';
            });
            
            this.isActive = false;
        }

        handleEmployeeSpecificActions() {
            // Handle Add Employee button click during tutorial
            const addEmployeeBtn = document.querySelector('#add-employee-btn');
            if (addEmployeeBtn) {
                addEmployeeBtn.addEventListener('click', () => {
                    if (this.isActive) {
                        // Store current tutorial state
                        localStorage.setItem('tutorial-flow', JSON.stringify({
                            flow: 'employee-addition',
                            returnPage: 'TA-employees',
                            step: this.currentStep
                        }));
                    }
                });
            }

            // Handle employee row clicks during tutorial
            const employeeRows = document.querySelectorAll('.employee-row');
            employeeRows.forEach(row => {
                row.addEventListener('click', (e) => {
                    if (this.isActive) {
                        // Prevent row click during tutorial unless specifically part of tutorial
                        if (!row.classList.contains('tutorial-target')) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }
                });
            });
        }

        handleEmployeeTableStep() {
            // Add visual cues for table interaction
            const table = document.querySelector('#employee-table');
            if (table) {
                // Add hover effect to first row
                const firstRow = table.querySelector('tbody tr');
                if (firstRow) {
                    firstRow.classList.add('tutorial-target');
                    firstRow.style.transition = 'background-color 0.3s ease';
                }
            }
        }

        // Add method to check if modal is open
        isModalOpen(modalId) {
            const modal = document.getElementById(modalId);
            return modal && (
                modal.classList.contains('show') || 
                modal.classList.contains('visible') || 
                modal.style.display === 'block'
            );
        }

        // Update waitForModal method to be more reliable
        async waitForModal(modalId, timeout = 2000) {
            const startTime = Date.now();
            while (Date.now() - startTime < timeout) {
                const modal = document.getElementById(modalId);
                if (modal && (
                    modal.classList.contains('show') || 
                    modal.classList.contains('visible') || 
                    modal.style.display === 'block' ||
                    window.getComputedStyle(modal).display !== 'none'
                )) {
                    // Add extra delay to ensure modal is fully visible
                    await new Promise(resolve => setTimeout(resolve, 300));
                    return true;
                }
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            return false;
        }
    }

    // Create and dispatch a custom event when tutorial engine is ready
    window.tutorialEngine = new TutorialEngine();
    document.dispatchEvent(new CustomEvent('tutorialEngineReady'));

    // Listen for tutorial start requests
    document.addEventListener('startTutorial', function() {
        const pageId = document.body.id;
        // Clear the completed state when manually starting tutorial
        localStorage.removeItem(`tutorial-done-${pageId}`);
        document.body.removeAttribute('data-tutorial-completed');
        window.tutorialEngine.init(pageId);
    });
})(); 