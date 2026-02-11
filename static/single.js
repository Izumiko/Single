/* ----

# Single Theme
# By: Dreamer-Paul
# Last Update: 2025.11.25

一个简洁大气，含夜间模式的 Typecho 博客模板。

本代码为奇趣保罗原创，并遵守 MIT 开源协议。欢迎访问我的博客：https://paugram.com

---- */

class Paul_Single {
    constructor(config) {
        this.config = config;
        this.body = document.body;
        this.content = ks.select(".post-content:not(.is-special), .page-content:not(.is-special)");

        this.init();
    }

    init() {
        this.initHeader();

        if (this.content) {
            this.initTree();
            this.initLinks();
            this.initCommentList();
        }

        this.initToTop();
        this.checkNightMode();
        this.initCopyright();

        this.logSignature();
    }

    // 菜单按钮逻辑
    initHeader() {
        const menu = document.querySelector(".head-menu");

        ks.select(".toggle-btn")?.addEventListener("click", () => {
            menu.classList.toggle("active");
        });

        ks.select(".light-btn")?.addEventListener("click", () => this.toggleNight());

        const searchBtn = document.querySelector(".search-btn");
        const searchBar = document.querySelector(".head-search");

        searchBtn?.addEventListener("click", () => {
            searchBar.classList.toggle("active");
        });
    }

    // 关灯切换
    toggleNight() {
        if (this.body.classList.contains("dark-theme")) {
            this.body.classList.remove("dark-theme");
            document.cookie = "night=false;path=/;max-age=21600";
        } else {
            this.body.classList.add("dark-theme");
            document.cookie = "night=true;path=/;max-age=21600";
        }
    }

    // 目录树
    initTree() {
        const wrap = ks.select(".wrap");
        // 使用 Spread syntax 将 NodeList 转换为 Array，方便使用数组方法
        const headings = [...this.content.querySelectorAll("h1, h2, h3, h4, h5, h6")];

        if (headings.length === 0) return;

        this.body.classList.add("has-trees");

        // 计算起始层级
        // 1. 提取所有标签后的数字 (h1 -> 1, h2 -> 2)
        const levels = headings.map(el => parseInt(el.tagName.substring(1)));
        // 2. 找出最小值作为起始层级 (如果没有标题，默认为 1)
        const firstLevel = levels.length > 0 ? Math.min(...levels) : 1;

        // 目录树节点
        const trees = ks.create("section", {
            class: "article-list",
            html: `<h4><span class="title">目录</span></h4>`
        });

        headings.forEach((t, index) => {
            const text = t.innerText;
            t.id = `title-${index}`;

            const level = Number(t.tagName.substring(1)) - firstLevel + 1;
            const className = `item-${level}`;

            trees.appendChild(ks.create("a", {
                class: className,
                text,
                href: `#title-${index}`
            }));
        });

        wrap.appendChild(trees);

        // 绑定按钮
        const buttons = ks.select("footer .buttons");
        const btn = ks.create("button", {
            class: "toggle-list",
            attr: [{ name: "title", value: "切换文章目录" }],
        });

        buttons?.appendChild(btn);
        btn.addEventListener("click", () => trees.classList.toggle("active"));
    }

    // 自动添加外链 _blank
    initLinks() {
        const linksEl = this.content.getElementsByTagName("a");

        for (const t of linksEl) {
            if (t.host !== location.host && !t.href.startsWith("javascript")) {
                t.target = "_blank";
                t.rel = "noopener noreferrer"; // 安全性增强
            }
        }
    }

    initCommentList() {
        ks(".comment-content [href^='#comment']").each(t => {
            const targetId = t.getAttribute("href");
            const item = ks.select(targetId);

            if(item){
                t.addEventListener("mouseover", () => item.classList.add("active"));
                t.addEventListener("mouseout", () => item.classList.remove("active"));
            }
        });
    }

    // 返回页首
    initToTop() {
        const btn = document.querySelector(".to-top");
        if(!btn) return;

        const checkScroll = () => {
            const scroll = document.documentElement.scrollTop || document.body.scrollTop;
            scroll >= window.innerHeight / 2
                ? btn.classList.add("active")
                : btn.classList.remove("active");
        };

        window.addEventListener("scroll", checkScroll, { passive: true });

        btn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }

    checkNightMode() {
        if (this.config.night) {
            const hour = new Date().getHours();
            // String.prototype.includes
            if (!document.cookie.includes("night") && (hour <= 5 || hour >= 22)) {
                this.body.classList.add("dark-theme");
                document.cookie = "night=true;path=/;max-age=21600";
            }
        } else if (document.cookie.includes("night")) {
            document.cookie.includes("night=true")
                ? this.body.classList.add("dark-theme")
                : this.body.classList.remove("dark-theme");
        }
    }

    initCopyright() {
        if (this.config.copyright) {
            document.addEventListener("copy", () => {
                ks.notice("复制内容请注明来源并保留版权信息！", { color: "yellow", overlay: true });
            });
        }
    }

    logSignature() {
        if (window.console && window.console.log) {
            console.log("%c Single %c https://paugram.com ", "color: #fff; margin: 1em 0; padding: 5px 0; background: #ffa628;", "margin: 1em 0; padding: 5px 0; background: #efefef;");
        }
    }
}

ks.image(".post-content:not(.is-special) img, .page-content:not(.is-special) img");
