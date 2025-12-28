// Vanilla JS Liveblog - No jQuery
(function () {
    'use strict';

    class EasyLiveblog {
        constructor(container) {
            this.container = container;
            this.documentTitle = document.title;
            this.firstLoad = true;
            this.newPosts = 0;
            this.timestamp = false;
            this.loader = null;
            this.showNewButton = null;
            this.loadMoreButton = null;
            this.list = null;
            this.statusMessage = null;
            this.settings = null;
            this.latestTimestamp = 0;
            this.lightbox = null;
            this.lightboxImg = null;

            this.init();
        }

        init() {
            if (!this.container) {
                return;
            }

            this.showNewButton = this.container.querySelector('#elb-show-new-posts');
            this.loadMoreButton = this.container.querySelector('#elb-load-more');
            this.loader = this.container.querySelector('.elb-loader');
            this.list = this.container.querySelector('.elb-liveblog-list');
            this.statusMessage = this.container.querySelector('.elb-liveblog-closed-message');

            this.initLightbox();

            // Initial fetch of the full feed
            this.fetchFeed();

            // Event listeners
            if (this.showNewButton) {
                this.showNewButton.addEventListener('click', () => this.showNew());
            }

            if (this.loadMoreButton) {
                this.loadMoreButton.addEventListener('click', () => this.loadMore());
            }

            // Content Protection
            this.initContentProtection();

            // Pagination Mode
            const widgetMode = this.container.dataset.paginationType;
            const globalMode = elb.pagination_type;
            this.paginationMode = widgetMode && widgetMode !== 'default' ? widgetMode : (globalMode || 'button');

            this.isLoading = false;
            this.observer = null;

            if (this.paginationMode === 'infinite') {
                if (this.loadMoreButton) {
                    this.loadMoreButton.style.display = 'none';
                }

                // Create a sentinel element for IntersectionObserver
                this.sentinel = document.createElement('div');
                this.sentinel.className = 'elb-infinite-scroll-sentinel';
                this.container.appendChild(this.sentinel);

                this.setupInfiniteScroll();
            }
        }

        setupInfiniteScroll() {
            if ('IntersectionObserver' in window) {
                this.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !this.isLoading) {
                            // Check if there are actually more posts to load
                            const hiddenPosts = this.list.querySelectorAll('li.elb-hide.elb-liveblog-initial-post');
                            if (hiddenPosts.length > 0) {
                                this.loadMore();
                            }
                        }
                    });
                }, {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.1
                });

                if (this.sentinel) {
                    this.observer.observe(this.sentinel);
                }
            } else {
                // Fallback for browsers not supporting IntersectionObserver
                // Show button instead
                if (this.loadMoreButton) {
                    this.loadMoreButton.style.display = 'block';
                }
            }
        }

        initContentProtection() {
            // Check global setting (via Settings)
            const globalProtection = elb.content_protection === '1' || elb.content_protection === true;

            // Check widget specific setting via data attribute
            const widgetProtection = this.container.dataset.contentProtection === '1';

            // Function to check if protection should be active
            if (globalProtection || widgetProtection) {

                // Apply CSS to prevent text selection
                this.container.style.userSelect = 'none';
                this.container.style.webkitUserSelect = 'none';
                this.container.style.mozUserSelect = 'none';
                this.container.style.msUserSelect = 'none';

                // Prevent right-click context menu
                this.container.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    return false;
                });

                // Prevent copy keyboard shortcuts (Ctrl+C / Cmd+C)
                this.container.addEventListener('keydown', (e) => {
                    // Check for Ctrl+C (Windows/Linux) or Cmd+C (Mac)
                    if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                        e.preventDefault();
                        return false;
                    }
                });

                // Prevent drag selection
                this.container.addEventListener('selectstart', (e) => {
                    e.preventDefault();
                    return false;
                });
            }
        }

        // Fetch the full feed content
        fetchFeed() {
            const url = this.getEndpoint();

            fetch(url)
                .then(response => response.json())
                .then(feed => {
                    this.settings = feed.settings || {};
                    const newPosts = [];

                    if (this.loader) {
                        this.loader.style.display = 'none';
                    }

                    // Remove old new posts tracking
                    const oldNewPosts = this.list.querySelectorAll('.elb-new');
                    oldNewPosts.forEach(post => post.remove());
                    this.resetUpdateCounter();

                    feed.updates.forEach((post, index) => {
                        // Track the latest timestamp from valid posts
                        if (post.timestamp && post.timestamp > this.latestTimestamp) {
                            this.latestTimestamp = parseInt(post.timestamp);
                        }

                        let goTo = false;
                        const currentPost = this.list.querySelector(`li[data-elb-post-id="${post.id}"]`);

                        // First load - render all posts
                        if (this.firstLoad) {
                            const postElement = this.renderPost(post);

                            if ((index + 1) > this.container.dataset.showEntries) {
                                postElement.classList.add('elb-hide', 'elb-liveblog-initial-post');
                                if (this.loadMoreButton) {
                                    this.loadMoreButton.style.display = 'block';
                                }
                            }

                            if (post.id == this.container.dataset.highlightedEntry) {
                                goTo = true;
                                postElement.classList.add('elb-liveblog-highlight');
                                postElement.classList.remove('elb-hide');
                            }

                            this.list.appendChild(postElement);

                            if (goTo) {
                                const highlightedPost = this.list.querySelector(`li[data-elb-post-id="${post.id}"]`);
                                if (highlightedPost) {
                                    window.scrollTo(0, highlightedPost.offsetTop);
                                }
                            }
                            return;
                        }

                        // Update existing post
                        if (!this.firstLoad && currentPost) {
                            const newPostElement = this.renderPost(post);

                            // Update time
                            const timeElement = currentPost.querySelector('time');
                            const newTimeElement = newPostElement.querySelector('time');
                            if (timeElement && newTimeElement) {
                                timeElement.replaceWith(newTimeElement);
                            }

                            // Update heading
                            const headingElement = currentPost.querySelector('.elb-liveblog-post-heading');
                            const newHeadingElement = newPostElement.querySelector('.elb-liveblog-post-heading');
                            if (headingElement && newHeadingElement) {
                                headingElement.replaceWith(newHeadingElement);
                            }

                            // Update content 
                            if (currentPost.querySelectorAll('.elb-liveblog-post-content iframe').length === 0) {
                                const contentElement = currentPost.querySelector('.elb-liveblog-post-content');
                                const newContentElement = newPostElement.querySelector('.elb-liveblog-post-content');
                                if (contentElement && newContentElement) {
                                    contentElement.replaceWith(newContentElement);
                                }
                            }
                            return;
                        }

                        // New post - display immediately
                        if (!this.firstLoad && !currentPost) {
                            const postElement = this.renderPost(post);
                            postElement.classList.add('elb-new');
                            this.newPosts++;
                            newPosts.push(postElement);
                        }
                    });

                    // Add new posts to the top
                    if (newPosts.length > 0) {
                        newPosts.reverse().forEach(postElement => {
                            this.list.insertBefore(postElement, this.list.firstChild);
                        });
                    }

                    // Callback hook
                    if (typeof elb_after_feed_load === 'function') {
                        elb_after_feed_load(feed);
                    }

                    this.firstLoad = false;

                    // Show empty message if needed
                    if (this.list.querySelectorAll('li').length === 0) {
                        const emptyMessage = this.container.querySelector('.elb-no-liveblog-entries-message');
                        if (emptyMessage) {
                            emptyMessage.style.display = 'block';
                        }
                    }

                    // Auto-show new posts
                    if (this.newPosts > 0) {
                        this.showNew();
                        if (typeof elb_after_update_liveblog_callback === 'function') {
                            elb_after_update_liveblog_callback();
                        }
                    }

                    // Update human timestamps
                    if (elb.datetime_format === 'human') {
                        this.updateTimestamps();
                    }

                    // Check status and schedule next poll
                    if (feed.status === 'closed') {
                        if (this.statusMessage) {
                            this.statusMessage.style.display = 'block';
                        }
                    } else {
                        setTimeout(() => this.poll(), elb.interval * 1000);
                    }
                })
                .catch(error => {
                    console.error('Easy Liveblogs: Error fetching feed', error);
                    setTimeout(() => this.poll(), elb.interval * 1000);
                });
        }

        // Lightweight check for updates
        poll() {
            const url = this.getEndpoint('/check');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'closed') {
                        if (this.statusMessage) {
                            this.statusMessage.style.display = 'block';
                        }
                        return; // Stop polling
                    }

                    // Compare timestamps. If server has newer post, fetch full feed
                    if (parseInt(data.timestamp) > this.latestTimestamp) {
                        this.fetchFeed();
                    } else {
                        // No new updates, just update human times and poll again
                        if (elb.datetime_format === 'human') {
                            this.updateTimestamps();
                        }
                        setTimeout(() => this.poll(), elb.interval * 1000);
                    }
                })
                .catch(error => {
                    console.error('Easy Liveblogs: Error checking updates', error);
                    setTimeout(() => this.poll(), elb.interval * 1000);
                });
        }

        initLightbox() {
            if (document.querySelector('.elb-lightbox')) {
                this.lightbox = document.querySelector('.elb-lightbox');
                this.lightboxImg = this.lightbox.querySelector('img');
                return;
            }

            const lightboxHTML = `<div class="elb-lightbox"><img src="" alt="Liveblog Lightbox"></div>`;
            document.body.insertAdjacentHTML('beforeend', lightboxHTML);

            this.lightbox = document.querySelector('.elb-lightbox');
            this.lightboxImg = this.lightbox.querySelector('img');

            this.lightbox.addEventListener('click', () => {
                this.lightbox.classList.remove('active');
            });
        }

        bindLightbox(container) {
            const images = container.querySelectorAll('.elb-liveblog-post-content img');
            images.forEach(img => {
                img.style.cursor = 'zoom-in';
                img.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.lightboxImg.src = img.src;
                    this.lightbox.classList.add('active');
                });
            });
        }

        renderPost(post) {
            const li = document.createElement('li');
            li.className = 'elb-liveblog-post';
            li.dataset.elbPostDatetime = post.timestamp;
            li.dataset.elbPostId = post.id;

            let html = '';
            html += this.renderTime(post);

            if (this.settings.showAuthor) {
                html += this.renderAuthor(post);
            }

            html += `<h2 class="elb-liveblog-post-heading">${post.title}</h2>`;
            html += `<div class="elb-liveblog-post-content">${post.content}</div>`;

            if (this.settings.showSharing) {
                html += this.renderSharing(post);
            }

            if (this.settings.isEditor) {
                html += this.renderActions(post);
            }

            li.innerHTML = html;
            this.bindLightbox(li);
            return li;
        }

        renderTime(post) {
            if (elb.datetime_format === 'human') {
                const timeAgo = this.getTimeAgo(post.datetime);
                return `<p class="elb-liveblog-post-time"><time class="elb-js-update-time" datetime="${post.datetime}">${timeAgo}</time></p>`;
            } else {
                return `<p class="elb-liveblog-post-time"><time datetime="${post.datetime}">${post.time}</time></p>`;
            }
        }

        renderAuthor(post) {
            const author = post.author ? this.escapeHtml(post.author) : '';
            return `<p class="elb-liveblog-post-author">By ${author}</p>`;
        }

        renderSharing(post) {
            const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(post.permalink)}`;
            const xUrl = `https://x.com/intent/tweet?text=${encodeURIComponent(post.title)} ${encodeURIComponent(post.permalink)}`;
            const mailUrl = `mailto:?&subject=${encodeURIComponent(post.title)}&body=${encodeURIComponent(post.permalink)}`;

            const icons = this.settings.socialIcons || {};
            const fbIcon = icons.facebook || '';
            const xIcon = icons.x || '';
            const mailIcon = icons.mail || '';

            return `
				<div class="elb-liveblog-post-sharing">
					<a href="${fbUrl}" target="_blank" title="Share via Facebook">
						${fbIcon}
					</a>
					<a href="${xUrl}" target="_blank" title="Share via X/Twitter">
						${xIcon}
					</a>
					<a href="${mailUrl}" target="_blank" title="Share via email">
						${mailIcon}
					</a>
				</div>
			`;
        }

        renderActions(post) {
            const editUrl = `${window.location.origin}/wp-admin/post.php?post=${post.id}&action=edit`;
            return `
				<div class="elb-liveblog-actions">
					<a href="${editUrl}" rel="nofollow">Edit This</a>
				</div>
			`;
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        getTime() {
            const d = new Date();
            const time = d.getTime();

            if (time === 0) {
                return 0;
            }
            return Math.round(time / 60000);
        }

        getEndpoint(path = '') {
            let url = this.container.dataset.endpoint;
            // Endpoint in dataset usually ends with /liveblog/{id}

            if (path) {
                url = url + path;
            }

            if (this.container.dataset.appendTimestamp === '1') {
                const separator = url.includes('?') ? '&' : '?';
                url = url + separator + '_=' + this.getTime();
            }

            return url;
        }

        showNew() {
            const newPosts = this.list.querySelectorAll('li.elb-new:not([style*="display: none"])');
            newPosts.forEach(post => {
                post.classList.remove('elb-new');
            });

            if (this.showNewButton) {
                this.showNewButton.style.display = 'none';
            }
            this.resetUpdateCounter();
        }

        resetUpdateCounter() {
            this.newPosts = 0;
            document.title = this.documentTitle;
        }

        loadMore() {
            if (this.isLoading) return;
            this.isLoading = true;

            // Show loader if infinite scroll
            if (this.paginationMode === 'infinite' && this.loader) {
                this.loader.style.display = 'block';
            }

            // Simulate network delay or just process immediate DOM changes
            // Since this plugin currently hides posts with CSS, the "load" is technically instant.
            // But we keep the structure for potential future AJAX loading.

            const hiddenPosts = this.list.querySelectorAll('li.elb-hide.elb-liveblog-initial-post');
            const showEntries = parseInt(this.container.dataset.showEntries) || 10;
            let count = 0;

            hiddenPosts.forEach((post, index) => {
                if (count < showEntries) {
                    post.classList.remove('elb-hide');
                    count++;
                }
            });

            this.isLoading = false;

            if (this.paginationMode === 'infinite' && this.loader) {
                this.loader.style.display = 'none';
            }

            // Check if any hidden posts remain
            if (this.list.querySelectorAll('li.elb-hide.elb-liveblog-initial-post').length === 0) {
                if (this.loadMoreButton) {
                    this.loadMoreButton.textContent = elb.now_more_posts;
                    // Fade out logic for button mode
                    if (this.paginationMode !== 'infinite') {
                        setTimeout(() => {
                            this.loadMoreButton.style.opacity = '0';
                            this.loadMoreButton.style.transition = 'opacity 1s';
                            setTimeout(() => {
                                this.loadMoreButton.style.display = 'none';
                            }, 1000);
                        }, 2000);
                    } else {
                        // Infinite scroll: just hide button (should be hidden anyway) and disconnect observer
                        this.loadMoreButton.style.display = 'none';
                        if (this.observer && this.sentinel) {
                            this.observer.unobserve(this.sentinel);
                        }
                    }
                }
            }

            if (typeof elb_after_load_more_callback === 'function') {
                elb_after_load_more_callback();
            }
        }

        updateTimestamps() {
            const posts = this.list.querySelectorAll('li');
            posts.forEach(post => {
                const timeElement = post.querySelector('.elb-js-update-time');
                if (timeElement) {
                    const datetime = timeElement.getAttribute('datetime');
                    timeElement.textContent = this.getTimeAgo(datetime);
                }
            });
        }

        getTimeAgo(datetime) {
            const date = new Date(datetime);
            const now = new Date();
            const diff = (now - date) / 1000;
            const locale = elb.locale.replace('_', '-');

            if (typeof Intl === 'undefined' || typeof Intl.RelativeTimeFormat === 'undefined') {
                return date.toLocaleDateString();
            }

            const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

            if (diff < 60) {
                return rtf.format(-Math.round(diff), 'seconds');
            } else if (diff < 3600) {
                return rtf.format(-Math.round(diff / 60), 'minutes');
            } else if (diff < 86400) {
                return rtf.format(-Math.round(diff / 3600), 'hours');
            } else if (diff < 2592000) {
                return rtf.format(-Math.round(diff / 86400), 'days');
            } else if (diff < 31536000) {
                return rtf.format(-Math.round(diff / 2592000), 'months');
            } else {
                return rtf.format(-Math.round(diff / 31536000), 'years');
            }
        }
    }

    // Expose Global Object for Elementor/External Use
    window.ELB = {
        init: function (container = null) {
            if (container) {
                // Initialize specific container (e.g., inside Elementor widget)
                const liveblogContainer = container.querySelector('.elb-liveblog');
                if (liveblogContainer && !liveblogContainer.dataset.elbInitialized) {
                    new EasyLiveblog(liveblogContainer);
                    liveblogContainer.dataset.elbInitialized = 'true';
                }
            } else {
                // Initialize all on page
                const containers = document.querySelectorAll('.elb-liveblog');
                containers.forEach(container => {
                    if (!container.dataset.elbInitialized) {
                        new EasyLiveblog(container);
                        container.dataset.elbInitialized = 'true';
                    }
                });
            }
        }
    };

    // Auto-init on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.ELB.init());
    } else {
        window.ELB.init();
    }

    // Elementor Hook
    window.addEventListener('elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction('frontend/element_ready/elb_liveblog.default', function ($scope) {
            window.ELB.init($scope[0]);
        });
    });
})();