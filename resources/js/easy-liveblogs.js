import moment from 'moment';

moment.locale(elb.locale);

// Vanilla JS Liveblog - No jQuery
(function () {
    'use strict';

    const liveblog = {
        container: null,
        documentTitle: null,
        firstLoad: true,
        newPosts: 0,
        timestamp: false,
        loader: null,
        showNewButton: null,
        loadMoreButton: null,
        list: null,
        statusMessage: null,
        settings: null,
        latestTimestamp: 0,

        init: function () {
            this.documentTitle = document.title;
            this.container = document.querySelector('.elb-liveblog');

            if (!this.container) {
                return;
            }

            this.showNewButton = this.container.querySelector('#elb-show-new-posts');
            this.loadMoreButton = this.container.querySelector('#elb-load-more');
            this.loader = this.container.querySelector('.elb-loader');
            this.list = this.container.querySelector('.elb-liveblog-list');
            this.statusMessage = this.container.querySelector('.elb-liveblog-closed-message');

            // Initial fetch of the full feed
            this.fetchFeed();

            // Event listeners
            if (this.showNewButton) {
                this.showNewButton.addEventListener('click', () => this.showNew());
            }

            if (this.loadMoreButton) {
                this.loadMoreButton.addEventListener('click', () => this.loadMore());
            }
        },

        // Fetch the full feed content
        fetchFeed: function () {
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
                        const emptyMessage = document.querySelector('.elb-no-liveblog-entries-message');
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
        },

        // Lightweight check for updates
        poll: function () {
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
        },

        renderPost: function (post) {
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
            return li;
        },

        renderTime: function (post) {
            if (elb.datetime_format === 'human') {
                const timeAgo = moment(post.datetime).fromNow();
                return `<p class="elb-liveblog-post-time"><time class="elb-js-update-time" datetime="${post.datetime}">${timeAgo}</time></p>`;
            } else {
                return `<p class="elb-liveblog-post-time"><time datetime="${post.datetime}">${post.time}</time></p>`;
            }
        },

        renderAuthor: function (post) {
            return `<p class="elb-liveblog-post-author">By ${this.escapeHtml(post.author)}</p>`;
        },

        renderSharing: function (post) {
            const fbUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(post.permalink)}`;
            const xUrl = `https://x.com/intent/tweet?text=${encodeURIComponent(post.title)} ${encodeURIComponent(post.permalink)}`;
            const mailUrl = `mailto:?&subject=${encodeURIComponent(post.title)}&body=${encodeURIComponent(post.permalink)}`;

            return `
				<div class="elb-liveblog-post-sharing">
					<a href="${fbUrl}" target="_blank" title="Share via Facebook">
						${this.settings.socialIcons.facebook}
					</a>
					<a href="${xUrl}" target="_blank" title="Share via X/Twitter">
						${this.settings.socialIcons.x}
					</a>
					<a href="${mailUrl}" target="_blank" title="Share via email">
						${this.settings.socialIcons.mail}
					</a>
				</div>
			`;
        },

        renderActions: function (post) {
            const editUrl = `${window.location.origin}/wp-admin/post.php?post=${post.id}&action=edit`;
            return `
				<div class="elb-liveblog-actions">
					<a href="${editUrl}" rel="nofollow">Edit This</a>
				</div>
			`;
        },

        escapeHtml: function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        getTime: function () {
            const d = new Date();
            const time = d.getTime();

            if (time === 0) {
                return 0;
            }
            return Math.round(time / 60000);
        },

        getEndpoint: function (path = '') {
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
        },

        showNew: function () {
            const newPosts = this.list.querySelectorAll('li.elb-new:not([style*="display: none"])');
            newPosts.forEach(post => {
                post.classList.remove('elb-new');
            });

            if (this.showNewButton) {
                this.showNewButton.style.display = 'none';
            }
            this.resetUpdateCounter();
        },

        resetUpdateCounter: function () {
            this.newPosts = 0;
            document.title = this.documentTitle;
        },

        loadMore: function () {
            const hiddenPosts = this.list.querySelectorAll('li.elb-hide.elb-liveblog-initial-post');
            const showEntries = parseInt(this.container.dataset.showEntries) || 10;

            hiddenPosts.forEach((post, index) => {
                if (showEntries > index) {
                    post.classList.remove('elb-hide');
                }
            });

            if (this.list.querySelectorAll('li.elb-hide.elb-liveblog-initial-post').length === 0) {
                if (this.loadMoreButton) {
                    this.loadMoreButton.textContent = elb.now_more_posts;
                    setTimeout(() => {
                        this.loadMoreButton.style.opacity = '0';
                        this.loadMoreButton.style.transition = 'opacity 1s';
                        setTimeout(() => {
                            this.loadMoreButton.style.display = 'none';
                        }, 1000);
                    }, 2000);
                }
            }

            if (typeof elb_after_load_more_callback === 'function') {
                elb_after_load_more_callback();
            }
        },

        updateTimestamps: function () {
            const posts = this.list.querySelectorAll('li');
            posts.forEach(post => {
                const timeElement = post.querySelector('.elb-js-update-time');
                if (timeElement) {
                    const datetime = timeElement.getAttribute('datetime');
                    timeElement.textContent = moment(datetime).fromNow();
                }
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => liveblog.init());
    } else {
        liveblog.init();
    }
})();