<?php

function devhire_home_copy(): array
{
    return [
        'hero' => [
            'eyebrow' => 'Developer Hiring Platform',
            'title' => 'Hire software developers with trusted matches',
            'lead' => 'DevHire connects companies with vetted software engineers across full stack, frontend, backend, mobile, cloud, data, and security roles. The platform is built to improve search visibility, trust, and application quality.',
            'primary_cta' => ['label' => 'Browse Open Jobs', 'url' => 'pages/jobs.php'],
            'secondary_cta' => ['label' => 'Explore Careers', 'url' => 'pages/careers.php'],
            'trust_points' => [
                'Verified developer profiles',
                'Transparent hiring process',
                'Remote, hybrid, and on-site roles',
            ],
        ],
        'about' => [
            'eyebrow' => 'About DevHire',
            'title' => 'A hiring platform built for modern engineering teams',
            'paragraphs' => [
                'DevHire was created to make technical hiring easier to evaluate, easier to trust, and easier to complete. We help employers find developers who can ship production-ready software, while helping candidates find roles where their skills are respected and developed.',
                'Instead of noisy job boards and shallow keyword matching, we focus on quality signals: experience level, technology fit, communication, portfolio strength, and long-term growth potential. That means stronger candidate matches and better outcomes for both sides of the hiring process.',
            ],
            'stats' => [
                ['label' => 'Application quality', 'value' => 'High-signal'],
                ['label' => 'Hiring model', 'value' => 'Vetted'],
                ['label' => 'Work styles', 'value' => 'Remote-first'],
                ['label' => 'Support', 'value' => 'Human-led'],
            ],
        ],
        'why_choose' => [
            ['icon' => 'fas fa-user-check', 'title' => 'Verified Talent', 'copy' => 'We prioritize strong profiles, real project evidence, and skills that map to current hiring needs.'],
            ['icon' => 'fas fa-bolt', 'title' => 'Faster Decisions', 'copy' => 'Structured profiles and clear role requirements help teams move from review to interview faster.'],
            ['icon' => 'fas fa-shield-alt', 'title' => 'Trust and Transparency', 'copy' => 'Candidates and employers know what to expect at every stage, from application to offer.'],
            ['icon' => 'fas fa-globe', 'title' => 'Global Reach', 'copy' => 'We support remote, hybrid, and location-based hiring across modern software teams.'],
            ['icon' => 'fas fa-code', 'title' => 'Engineering Fit', 'copy' => 'Roles are matched to stacks, seniority, architecture needs, and delivery expectations.'],
            ['icon' => 'fas fa-chart-line', 'title' => 'Growth Mindset', 'copy' => 'We highlight learning, mentorship, and long-term career progression, not just the next job.'],
        ],
        'technologies' => [
            ['name' => 'Frontend', 'icon' => 'fab fa-react'],
            ['name' => 'Backend', 'icon' => 'fas fa-server'],
            ['name' => 'Mobile', 'icon' => 'fas fa-mobile-alt'],
            ['name' => 'Cloud & DevOps', 'icon' => 'fas fa-cloud'],
            ['name' => 'Databases', 'icon' => 'fas fa-database'],
            ['name' => 'AI / ML', 'icon' => 'fas fa-brain'],
            ['name' => 'Cybersecurity', 'icon' => 'fas fa-lock'],
            ['name' => 'UI / UX', 'icon' => 'fas fa-pen-ruler'],
            ['name' => 'QA Engineering', 'icon' => 'fas fa-vial'],
            ['name' => 'Systems', 'icon' => 'fas fa-network-wired'],
        ],
        'opportunities' => [
            ['title' => 'Full Stack Developer', 'mode' => 'Remote-friendly', 'copy' => 'Build end-to-end products with React, Node.js, PHP, Laravel, or Python across modern web stacks.'],
            ['title' => 'Frontend Developer', 'mode' => 'Hybrid and remote', 'copy' => 'Own interfaces, component systems, accessibility, performance, and responsive product experiences.'],
            ['title' => 'Backend Developer', 'mode' => 'Distributed teams', 'copy' => 'Design APIs, integrate services, secure data flows, and scale business logic for production systems.'],
            ['title' => 'DevOps Engineer', 'mode' => 'Cloud-first', 'copy' => 'Improve delivery pipelines, infrastructure reliability, observability, and release confidence.'],
        ],
        'process' => [
            ['step' => '01', 'title' => 'Apply or post a role', 'copy' => 'Create a profile or publish a role with the stack, seniority, and outcomes you need.'],
            ['step' => '02', 'title' => 'Review the fit', 'copy' => 'Evaluate applications using a concise skills-first view that saves time and reduces noise.'],
            ['step' => '03', 'title' => 'Interview with clarity', 'copy' => 'Move qualified candidates into technical and cultural interviews with a transparent agenda.'],
            ['step' => '04', 'title' => 'Hire and onboard', 'copy' => 'Close strong candidates faster and set them up with the context they need to succeed.'],
        ],
        'culture' => [
            'title' => 'Engineering Culture',
            'paragraphs' => [
                'We value ownership, thoughtful code review, practical documentation, and product awareness. Great engineering teams do more than write code; they reduce friction, collaborate clearly, and ship reliable software that helps the business move forward.',
                'The platform highlights teams that invest in maintainability, testing discipline, secure development practices, and a healthy work cadence. That is the kind of environment developers want and employers remember.',
            ],
        ],
        'growth' => [
            'title' => 'Career Growth',
            'items' => [
                'Mentorship from experienced engineers and hiring leaders',
                'Exposure to product, architecture, and business decisions',
                'Opportunities to deepen expertise in in-demand stacks',
                'Clear paths from junior to mid-level to senior roles',
            ],
        ],
        'benefits' => [
            'title' => 'Benefits & Perks',
            'items' => [
                'Flexible remote and hybrid options',
                'Competitive compensation packages',
                'Modern tools and collaborative workflows',
                'Learning budgets and conference access',
                'Paid time off and healthy work-life balance',
                'Teams that respect focus time and deep work',
            ],
        ],
        'industry_focus' => [
            'title' => 'Industry Focus',
            'items' => [
                'SaaS and product engineering',
                'Fintech and digital payments',
                'Healthcare and healthtech',
                'E-commerce and marketplace platforms',
                'EdTech and learning platforms',
                'Logistics, travel, and on-demand services',
            ],
        ],
        'stats' => [
            ['value' => '500+', 'label' => 'Applications reviewed'],
            ['value' => '120+', 'label' => 'Developers hired'],
            ['value' => '50+', 'label' => 'Partner companies'],
            ['value' => '95%', 'label' => 'Satisfaction rate'],
        ],
        'testimonials' => [
            ['name' => 'Arjun Patel', 'role' => 'Full Stack Developer', 'quote' => 'DevHire helped me find a remote role with a team that values clean architecture, communication, and long-term growth.'],
            ['name' => 'Priya Sharma', 'role' => 'Frontend Developer', 'quote' => 'The process was transparent, the opportunities were relevant, and I never felt like I was sending applications into a void.'],
            ['name' => 'Rohit Verma', 'role' => 'Backend Developer', 'quote' => 'I got interviews faster because the platform matched my experience with the right technical expectations.'],
        ],
        'faq' => [
            ['q' => 'Who is DevHire built for?', 'a' => 'DevHire is built for software developers, engineering managers, recruiters, founders, and hiring teams that want higher-quality technical hiring outcomes.'],
            ['q' => 'What kinds of roles are featured?', 'a' => 'We focus on software developer jobs across full stack, frontend, backend, mobile, cloud, data, DevOps, UI/UX, QA, AI, ML, and security roles.'],
            ['q' => 'Is the platform suitable for remote hiring?', 'a' => 'Yes. Remote developer jobs are a core part of the platform, and many employers are open to distributed or hybrid teams.'],
            ['q' => 'How do candidates apply?', 'a' => 'Candidates create a profile, upload a resume, and apply to roles that match their skills and experience.'],
            ['q' => 'How do companies post roles?', 'a' => 'Employers can publish detailed job posts, review applicants, and manage hiring from a centralized workflow.'],
            ['q' => 'Why is DevHire better than generic job boards?', 'a' => 'We emphasize skill match, hiring clarity, and quality applications, which helps teams spend less time filtering and more time interviewing relevant candidates.'],
            ['q' => 'Can I find PHP, Laravel, Node.js, and Python jobs here?', 'a' => 'Yes. The platform is designed to support hiring across popular web and backend stacks, including PHP developer jobs and Laravel developer jobs.'],
            ['q' => 'Do you support early-career developers?', 'a' => 'Yes. We include junior-friendly opportunities as well as senior and leadership roles across the engineering spectrum.'],
        ],
        'final_cta' => [
            'title' => 'Ready to hire or apply with confidence?',
            'copy' => 'Explore active roles, connect with verified companies, and move one step closer to the right engineering opportunity.',
        ],
    ];
}

function devhire_about_copy(): array
{
    return [
        'story' => [
            'title' => 'Company Story',
            'paragraphs' => [
                'DevHire started with a simple idea: technical hiring should feel intelligent, respectful, and outcome-focused. Developers deserve opportunities that match their abilities, and companies deserve a process that surfaces the right people without wasting time.',
                'We built the platform to reduce hiring friction, improve candidate quality, and make it easier for teams to make confident decisions. Every part of the experience is designed to support trust, clarity, and long-term career fit.',
            ],
        ],
        'mission' => 'Our mission is to connect exceptional software talent with companies that value craftsmanship, ownership, and measurable impact.',
        'vision' => 'Our vision is to become the most trusted developer hiring platform for modern product teams across remote and global markets.',
        'values' => [
            ['title' => 'Quality First', 'copy' => 'We care about strong signals, real skills, and meaningful work.'],
            ['title' => 'Transparency', 'copy' => 'We keep expectations clear for candidates and employers.'],
            ['title' => 'Respect', 'copy' => 'We treat every applicant and hiring team with professionalism.'],
            ['title' => 'Growth', 'copy' => 'We support career progression, learning, and better roles over time.'],
            ['title' => 'Integrity', 'copy' => 'We value honest communication and dependable outcomes.'],
            ['title' => 'Inclusivity', 'copy' => 'We believe diverse teams build better products and stronger cultures.'],
        ],
        'leadership' => [
            'title' => 'Leadership Philosophy',
            'paragraphs' => [
                'Great leadership in hiring means creating a system where good people can do their best work. That requires discipline in evaluation, empathy in communication, and accountability in execution.',
                'We believe leaders should remove noise, focus on measurable results, and build conditions where engineers can contribute without unnecessary process overhead.',
            ],
        ],
        'diversity' => [
            'title' => 'Diversity & Inclusion',
            'paragraphs' => [
                'We support hiring environments that welcome different backgrounds, perspectives, and career paths. Strong engineering teams are built on inclusion, not sameness.',
                'Our goal is to help companies create fairer hiring journeys where talent is evaluated on potential, evidence, communication, and practical skill.',
            ],
        ],
        'excellence' => [
            'title' => 'Engineering Excellence',
            'items' => [
                'Clear architecture and maintainable systems',
                'Reliable testing and release discipline',
                'Security-aware development practices',
                'Accessible, performant user experiences',
                'Strong collaboration between product and engineering',
            ],
        ],
    ];
}

function devhire_careers_copy(): array
{
    return [
        'why' => [
            'title' => 'Why Work Here',
            'paragraphs' => [
                'Working at DevHire means contributing to a platform that helps people grow their careers and helps companies build better teams. The work has visible impact, practical purpose, and long-term value.',
                'We encourage thoughtful ownership, direct communication, and a balanced approach to speed and quality. You will work on systems that matter to real candidates and real employers.',
            ],
        ],
        'open_roles' => [
            ['title' => 'Product Engineer', 'copy' => 'Build core platform experiences, job flows, and candidate journeys.'],
            ['title' => 'Frontend Engineer', 'copy' => 'Create responsive, high-converting user interfaces with strong performance.'],
            ['title' => 'Backend Engineer', 'copy' => 'Design reliable services, APIs, and data workflows that scale.'],
            ['title' => 'Growth Marketer', 'copy' => 'Improve search visibility, content quality, and audience acquisition.'],
            ['title' => 'Talent Partner', 'copy' => 'Help employers hire and help developers find stronger opportunities.'],
        ],
        'hiring_process' => [
            ['title' => 'Application Review', 'copy' => 'We review experience, fit, and portfolio evidence with a practical lens.'],
            ['title' => 'Role Conversation', 'copy' => 'We discuss expectations, scope, and how your work style matches the team.'],
            ['title' => 'Technical Interview', 'copy' => 'We explore problem solving, architecture thinking, and communication quality.'],
            ['title' => 'Final Discussion', 'copy' => 'We align on growth, compensation, and the environment you will join.'],
        ],
        'benefits' => [
            'title' => 'Employee Benefits',
            'items' => [
                'Flexible schedules and remote support',
                'Competitive compensation and recognition',
                'Health and wellness-minded culture',
                'Practical tools and modern workflows',
                'Paid learning time and career growth support',
            ],
        ],
        'learning' => [
            'title' => 'Learning Programs',
            'items' => [
                'Mentorship from experienced operators',
                'Knowledge sharing across product and engineering',
                'Conference, book, and course support',
                'Hands-on opportunities to learn new stacks',
            ],
        ],
        'development' => [
            'title' => 'Career Development',
            'paragraphs' => [
                'We support growth through coaching, challenge, and accountability. Whether you want to deepen your technical depth, become a people leader, or expand into product and growth, we encourage meaningful development paths.',
                'The goal is not just to fill roles. The goal is to build sustainable careers and teams that improve over time.',
            ],
        ],
    ];
}

function devhire_technology_sections(): array
{
    return [
        [
            'title' => 'Frontend Development',
            'overview' => 'Frontend developers turn product strategy into fast, accessible, and visually polished digital experiences.',
            'opportunities' => 'React developer jobs, Vue and Angular roles, design system work, UI engineering, and senior product frontend positions.',
            'skills' => 'JavaScript, TypeScript, React, CSS, responsive design, accessibility, state management, component architecture, and performance tuning.',
            'demand' => 'Demand remains strong because every modern company needs interfaces that improve conversion, retention, and user satisfaction.',
            'growth' => 'Frontend careers continue to expand into design systems, platform engineering, performance strategy, and full product ownership.',
        ],
        [
            'title' => 'Backend Development',
            'overview' => 'Backend developers create the systems, APIs, and services that power product logic and data movement.',
            'opportunities' => 'PHP developer jobs, Laravel developer jobs, Node.js developer jobs, Python developer jobs, and Java backend roles.',
            'skills' => 'APIs, databases, authentication, security, caching, testing, queue systems, microservices, and scalable architecture.',
            'demand' => 'Backend talent is consistently in demand because every digital product depends on reliable services and data handling.',
            'growth' => 'Backend engineers often progress into staff, platform, and architecture roles with stronger influence on technical direction.',
        ],
        [
            'title' => 'Mobile Development',
            'overview' => 'Mobile developers create native and cross-platform app experiences for iOS and Android users.',
            'opportunities' => 'Mobile app developer jobs across Flutter, React Native, Swift, Kotlin, and product-led mobile teams.',
            'skills' => 'Mobile UI patterns, offline support, app performance, APIs, testing, app store release workflows, and device compatibility.',
            'demand' => 'Mobile remains a high-value channel for consumer and enterprise products that need reliable app experiences.',
            'growth' => 'Mobile specialists can grow into lead app architect, product engineering, and platform leadership roles.',
        ],
        [
            'title' => 'Cloud & DevOps',
            'overview' => 'Cloud and DevOps engineers improve deployment speed, infrastructure reliability, and operational confidence.',
            'opportunities' => 'DevOps engineer jobs, cloud engineer careers, SRE roles, infrastructure automation, and platform engineering jobs.',
            'skills' => 'CI/CD, containers, observability, AWS, Azure, GCP, Docker, Kubernetes, Terraform, and incident response.',
            'demand' => 'Companies increasingly need engineers who can balance delivery velocity with stability, security, and cost control.',
            'growth' => 'Cloud careers often grow into architecture, platform ownership, reliability leadership, and engineering operations strategy.',
        ],
        [
            'title' => 'Database Engineering',
            'overview' => 'Database engineers make data trustworthy, performant, and available to the teams that need it most.',
            'opportunities' => 'Database administration, data modeling, performance tuning, analytics engineering, and database platform roles.',
            'skills' => 'SQL, schema design, indexing, replication, query optimization, backups, migration planning, and data governance.',
            'demand' => 'Data quality is central to reporting, product logic, and customer experience, which keeps this skillset in demand.',
            'growth' => 'Database engineers can grow into data platform, analytics architecture, and reliability-focused leadership paths.',
        ],
        [
            'title' => 'Artificial Intelligence',
            'overview' => 'AI roles help companies automate decisions, improve search, and create smarter software experiences.',
            'opportunities' => 'AI engineer jobs, applied AI product roles, prompt engineering, and intelligent system design opportunities.',
            'skills' => 'Model integration, evaluation, prompt design, APIs, responsible AI, data handling, and product experimentation.',
            'demand' => 'AI adoption is accelerating across product, support, analytics, and internal tooling teams.',
            'growth' => 'AI careers are expanding into applied research, product strategy, tooling, and platform specialization.',
        ],
        [
            'title' => 'Machine Learning',
            'overview' => 'Machine learning professionals build predictive systems that learn from data and improve with feedback.',
            'opportunities' => 'ML engineer jobs, data science adjacent roles, recommendation systems, forecasting, and personalization work.',
            'skills' => 'Python, model training, feature engineering, evaluation metrics, experimentation, and scalable deployment.',
            'demand' => 'Demand is strong in companies that rely on personalization, automation, fraud detection, and intelligent recommendations.',
            'growth' => 'ML careers can progress into model operations, applied research, experimentation leadership, and data platform ownership.',
        ],
        [
            'title' => 'Cybersecurity',
            'overview' => 'Cybersecurity roles protect products, customers, and business operations from growing digital threats.',
            'opportunities' => 'Security engineer jobs, application security, cloud security, compliance-focused roles, and threat response work.',
            'skills' => 'Threat modeling, vulnerability management, secure coding, identity controls, incident response, and risk assessment.',
            'demand' => 'Security talent is critical because every software team must protect user data, systems, and trust.',
            'growth' => 'Security careers can grow into architecture, governance, security leadership, and platform risk management.',
        ],
        [
            'title' => 'UI / UX Design',
            'overview' => 'Design professionals make products easier to understand, easier to use, and more compelling to adopt.',
            'opportunities' => 'UI UX designer careers, product design roles, interaction design, design systems, and research-driven product teams.',
            'skills' => 'Wireframing, prototypes, research, information architecture, accessibility, visual design, and collaboration with engineers.',
            'demand' => 'Companies invest heavily in design because better experience directly improves conversion, retention, and trust.',
            'growth' => 'Designers can advance into product design leadership, design systems, research, and cross-functional strategy.',
        ],
        [
            'title' => 'QA Engineering',
            'overview' => 'QA engineers improve product reliability by testing features, validating workflows, and catching defects early.',
            'opportunities' => 'Manual QA, test automation, quality engineering, release validation, and performance testing jobs.',
            'skills' => 'Test planning, automation tools, regression testing, API testing, defect tracking, and attention to detail.',
            'demand' => 'Strong QA is essential for fast-moving teams that want to ship confidently without sacrificing product quality.',
            'growth' => 'QA professionals can grow into automation leadership, release engineering, and quality strategy roles.',
        ],
    ];
}

function devhire_faq_items(): array
{
    return [
        ['category' => 'Getting Started', 'q' => 'How do I create a strong developer profile?', 'a' => 'Focus on relevant skills, measurable outcomes, portfolio links, and the technologies you use most often.'],
        ['category' => 'Getting Started', 'q' => 'What should employers include in a job post?', 'a' => 'A good post should explain the stack, seniority, work mode, responsibilities, salary range, and growth expectations.'],
        ['category' => 'Getting Started', 'q' => 'Can I apply without years of experience?', 'a' => 'Yes. Entry-level and early-career candidates can still be strong matches when they show potential, projects, and learning discipline.'],
        ['category' => 'Getting Started', 'q' => 'Do I need a portfolio to apply?', 'a' => 'A portfolio is strongly recommended because it helps hiring teams understand your real-world skills faster.'],
        ['category' => 'Getting Started', 'q' => 'How many roles can I apply to?', 'a' => 'You can apply to as many relevant roles as fit your background, but each application should be targeted and genuine.'],
        ['category' => 'Getting Started', 'q' => 'How do companies know applications are relevant?', 'a' => 'Each role is matched against clear skill and experience signals so employers can review with better context.'],
        ['category' => 'Hiring Process', 'q' => 'How long does the hiring process usually take?', 'a' => 'That depends on the employer, but well-structured roles often move from application to interview faster than generic job board leads.'],
        ['category' => 'Hiring Process', 'q' => 'What happens after I apply?', 'a' => 'Your profile is reviewed against the role requirements, and qualified candidates may be invited to the next step.'],
        ['category' => 'Hiring Process', 'q' => 'What do employers look for first?', 'a' => 'They usually want to see role fit, technical relevance, communication quality, and evidence that you can deliver results.'],
        ['category' => 'Hiring Process', 'q' => 'Do technical interviews always include coding tests?', 'a' => 'Not always. Many teams use a mix of portfolio review, architecture discussion, pair work, and practical coding exercises.'],
        ['category' => 'Hiring Process', 'q' => 'Can the process support multiple interview stages?', 'a' => 'Yes. The platform can support structured hiring flows from initial screening through final selection.'],
        ['category' => 'Hiring Process', 'q' => 'How can I improve my chances of getting interviews?', 'a' => 'Tailor your profile to the role, highlight outcomes, and show experience that is clearly connected to the stack being hired for.'],
        ['category' => 'Remote Work', 'q' => 'Are remote developer jobs available?', 'a' => 'Yes. Remote work is a major part of the platform and many employers actively look for distributed talent.'],
        ['category' => 'Remote Work', 'q' => 'Do you also have hybrid and on-site roles?', 'a' => 'Yes. We support remote, hybrid, and location-based roles so companies can hire in the model that fits their needs.'],
        ['category' => 'Remote Work', 'q' => 'What makes a remote candidate stand out?', 'a' => 'Clear communication, strong ownership, a documented workflow, and a history of delivering independently matter a lot.'],
        ['category' => 'Remote Work', 'q' => 'How should remote teams evaluate candidates?', 'a' => 'Look for written clarity, reliable delivery habits, collaboration skills, and comfort with asynchronous work.'],
        ['category' => 'Remote Work', 'q' => 'Can companies hire internationally?', 'a' => 'Yes, many hiring flows are well suited to global and cross-border collaboration.'],
        ['category' => 'Remote Work', 'q' => 'How do I show I can work remotely?', 'a' => 'Share projects that demonstrate autonomy, communication, and the ability to deliver without constant supervision.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature React developer jobs?', 'a' => 'Yes. React is one of the most in-demand frontend skill sets and appears across many product teams.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature PHP and Laravel developer jobs?', 'a' => 'Yes. PHP and Laravel remain highly relevant for product teams that need fast, maintainable web development.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature Node.js developer jobs?', 'a' => 'Yes. Node.js remains a strong choice for API development, real-time systems, and full stack roles.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature Python developer jobs?', 'a' => 'Yes. Python is popular for backend development, automation, data workflows, AI, and machine learning work.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature Java developer jobs?', 'a' => 'Yes. Java is still common in enterprise systems, backend platforms, and large-scale software teams.'],
        ['category' => 'Skills and Roles', 'q' => 'Do you feature DevOps engineer jobs?', 'a' => 'Yes. Cloud and DevOps roles are included because infrastructure reliability is essential to software delivery.'],
        ['category' => 'Career Growth', 'q' => 'Can early-career developers use the platform?', 'a' => 'Yes. We include opportunities for junior developers and candidates who are actively building experience.'],
        ['category' => 'Career Growth', 'q' => 'How can I grow from mid-level to senior?', 'a' => 'Focus on ownership, code quality, system thinking, mentorship, and consistent delivery on business-critical work.'],
        ['category' => 'Career Growth', 'q' => 'Are learning and development important to employers?', 'a' => 'Yes. Companies often prefer candidates who keep learning and can adapt to changing tools and priorities.'],
        ['category' => 'Career Growth', 'q' => 'Should I apply to stretch roles?', 'a' => 'Yes, if you have a realistic path to the role and can explain how your skills translate to the responsibilities.'],
        ['category' => 'Career Growth', 'q' => 'What helps someone get promoted in engineering?', 'a' => 'Reliable delivery, good judgment, communication, collaboration, and increased ownership usually matter most.'],
        ['category' => 'Career Growth', 'q' => 'How does DevHire support long-term careers?', 'a' => 'We highlight roles that include growth potential, learning, and teams that value strong engineering habits.'],
        ['category' => 'Trust and Quality', 'q' => 'How does DevHire build trust with users?', 'a' => 'Through clear role information, a professional experience, and a focus on quality over volume.'],
        ['category' => 'Trust and Quality', 'q' => 'Why is quality more important than quantity?', 'a' => 'A smaller number of relevant applications usually creates better hiring outcomes than a flood of mismatched ones.'],
        ['category' => 'Trust and Quality', 'q' => 'How do you keep the platform professional?', 'a' => 'We use structured content, clear expectations, and a hiring-first approach to keep the experience focused and credible.'],
        ['category' => 'Trust and Quality', 'q' => 'Can companies use DevHire for branding?', 'a' => 'Yes. Strong role descriptions and clear employer messaging improve candidate interest and search visibility.'],
        ['category' => 'Trust and Quality', 'q' => 'What makes a role post SEO-friendly?', 'a' => 'Using clear titles, skill terms, location details, and outcome-focused descriptions helps search visibility and relevance.'],
        ['category' => 'Trust and Quality', 'q' => 'Is support available if I need help?', 'a' => 'Yes. The platform is designed to feel human-led, so users can get guidance when they need it.'],
        ['category' => 'Industries', 'q' => 'Which industries hire through DevHire?', 'a' => 'We focus on SaaS, fintech, healthcare, e-commerce, EdTech, logistics, travel, and other technology-led industries.'],
        ['category' => 'Industries', 'q' => 'Are startup roles included?', 'a' => 'Yes. Startups often need versatile engineers who can move quickly and contribute across the stack.'],
        ['category' => 'Industries', 'q' => 'Are enterprise roles included?', 'a' => 'Yes. We also support roles that require scale, process maturity, and large-team collaboration.'],
        ['category' => 'Industries', 'q' => 'Do companies post contract and full-time roles?', 'a' => 'Yes. Roles can vary by work model depending on the company and project needs.'],
        ['category' => 'Industries', 'q' => 'Can I find product and platform engineering jobs?', 'a' => 'Yes. Product engineering and platform-focused roles are an important part of the technology hiring mix.'],
        ['category' => 'Industries', 'q' => 'How do I know which roles fit my background best?', 'a' => 'Look for clear stack alignment, seniority match, and responsibilities that reflect your past outcomes.'],
    ];
}

function devhire_blog_content(): array
{
    $topics = [
        ['label' => 'React Developers', 'keyword' => 'react developer jobs'],
        ['label' => 'PHP Developers', 'keyword' => 'php developer jobs'],
        ['label' => 'Laravel Developers', 'keyword' => 'laravel developer jobs'],
        ['label' => 'Node.js Developers', 'keyword' => 'node js developer jobs'],
        ['label' => 'Python Developers', 'keyword' => 'python developer jobs'],
        ['label' => 'Java Developers', 'keyword' => 'java developer jobs'],
        ['label' => 'Remote Developers', 'keyword' => 'remote developer jobs'],
        ['label' => 'Frontend Developers', 'keyword' => 'frontend developer careers'],
        ['label' => 'Backend Developers', 'keyword' => 'backend developer careers'],
        ['label' => 'DevOps Engineers', 'keyword' => 'devops engineer jobs'],
    ];

    $templates = [
        'How to Hire %s in 2026',
        'The Complete Guide to %s Hiring',
        '%s Interview Questions Every Team Should Ask',
        'What %s Candidates Expect From Employers',
        'Salary Trends for %s in 2026',
        'Skills That Make %s Stand Out',
        'How to Write Better %s Job Descriptions',
        'Remote Hiring Tips for %s Teams',
        'The Best Portfolio Signals for %s Applicants',
        'Why %s Roles Are Still in High Demand',
    ];

    $titles = [];
    foreach ($topics as $topic) {
        foreach ($templates as $template) {
            $titles[] = sprintf($template, $topic['label']);
        }
    }

    $categories = [
        'Hiring Guides' => ['Developer hiring strategies', 'Interview process design', 'Job description optimization'],
        'Career Advice' => ['Portfolio improvement', 'Resume optimization', 'Interview preparation'],
        'Technology Hiring' => ['Frontend recruiting', 'Backend recruiting', 'Cloud and DevOps hiring'],
        'Remote Work' => ['Distributed teams', 'Global hiring', 'Async collaboration'],
        'Engineering Culture' => ['Team structure', 'Code quality', 'Career progression'],
    ];

    return [
        'topics' => $topics,
        'keywords' => [
            'Software Developer Jobs',
            'Full Stack Developer Jobs',
            'React Developer Jobs',
            'PHP Developer Jobs',
            'Laravel Developer Jobs',
            'Node.js Developer Jobs',
            'Python Developer Jobs',
            'Java Developer Jobs',
            'Remote Developer Jobs',
            'Frontend Developer Careers',
            'Backend Developer Careers',
            'Mobile App Developer Jobs',
            'DevOps Engineer Jobs',
            'Cloud Engineer Careers',
            'UI UX Designer Careers',
            'Technology Careers',
            'Software Engineering Careers',
        ],
        'categories' => $categories,
        'titles' => $titles,
    ];
}

function devhire_testimonials(): array
{
    return [
        ['name' => 'Arjun Patel', 'role' => 'Full Stack Developer', 'quote' => 'DevHire gave me better matches, better conversations, and a much better hiring experience.'],
        ['name' => 'Priya Sharma', 'role' => 'Frontend Developer', 'quote' => 'I found a role that matched my stack and my growth goals instead of a generic listing.'],
        ['name' => 'Rohit Verma', 'role' => 'Backend Developer', 'quote' => 'The platform made it easier to present my skills clearly and get relevant interviews faster.'],
        ['name' => 'Anika Desai', 'role' => 'DevOps Engineer', 'quote' => 'I appreciated the focus on technical clarity and real expectations from the start.'],
        ['name' => 'Marcus Lee', 'role' => 'Engineering Manager', 'quote' => 'We saw stronger applications because the role structure attracted better-qualified candidates.'],
        ['name' => 'Nadia Khan', 'role' => 'Talent Partner', 'quote' => 'DevHire helps us screen faster while still keeping the process respectful and professional.'],
    ];
}
