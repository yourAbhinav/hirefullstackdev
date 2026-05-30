import { Job, Developer, Application, Testimonial } from './types';

export const INITIAL_JOBS: Job[] = [
  {
    id: 'job-1',
    title: 'Senior Full Stack Developer',
    companyName: 'Stripe',
    companyLogo: '💳',
    description: 'We are looking for a Senior Full Stack Engineer to design and implement highly secure, latency-sensitive payment infrastructure. You will work with Node.js, React, TypeScript, and AWS cloud systems.',
    requirements: [
      '5+ years of experience with React & Node.js',
      'Strong SQL database designs and profiling',
      'In-depth knowledge of payment APIs and secure session scopes'
    ],
    salary: '$140k - $185k',
    location: 'San Francisco, CA',
    experienceLevel: 'Senior',
    workType: 'Remote',
    postedAt: '2026-05-28',
    category: 'Full Stack',
    featured: true
  },
  {
    id: 'job-2',
    title: 'Lead Frontend Engineer',
    companyName: 'Notion',
    companyLogo: '📓',
    description: 'Help build the future of collaborative knowledge workspaces. Develop fluid canvas elements, rich text editors, and offline-persistent localized states with React and Tailwind CSS.',
    requirements: [
      'Expertise in CSS/SVG layout pipelines and layout optimizations',
      'Solid experience with state managers (Redux, Zustand, Recoil)',
      'Familiarity with collaborative socket protocols or CRDTs'
    ],
    salary: '$130k - $165k',
    location: 'Remote, US',
    experienceLevel: 'Senior',
    workType: 'Remote',
    postedAt: '2026-05-27',
    category: 'Frontend',
    featured: true
  },
  {
    id: 'job-3',
    title: 'NodeJS Backend Architect',
    companyName: 'Vercel',
    companyLogo: '▲',
    description: 'Architect next-generation edge execution runtimes and serverless frameworks. You will optimize memory scaling, micro-VM execution, and database connection pooling modules.',
    requirements: [
      'In-depth mastery of Node.js eventloop and native C++ buffers',
      'Familiarity with serverless runtimes and content delivery routing networks',
      'Outstanding proficiency in typed functional systems (TypeScript/Rust)'
    ],
    salary: '$150k - $210k',
    location: 'New York, NY',
    experienceLevel: 'Senior',
    workType: 'Hybrid',
    postedAt: '2026-05-25',
    category: 'Backend',
    featured: true
  },
  {
    id: 'job-4',
    title: 'Full Stack Developer',
    companyName: 'Clerk Inc.',
    companyLogo: '🔑',
    description: 'Grow our secure authentication widget portfolio. You will specialize in OAuth adapters, multi-tenant session syncing, and clean developer SDK wrapper architectures.',
    requirements: [
      '2+ years experience building web applications in React & Node',
      'Experience with OAuth 2.0 flow mechanisms, cookies, and tokens',
      'Detail-oriented UI developer with eye for micro-animations'
    ],
    salary: '$95k - $125k',
    location: 'San Francisco, CA',
    experienceLevel: 'Mid',
    workType: 'On-site',
    postedAt: '2026-05-24',
    category: 'Full Stack',
    featured: false
  },
  {
    id: 'job-5',
    title: 'Junior PHP Full Stack Engineer',
    companyName: 'DevHire Tech',
    companyLogo: '💻',
    description: 'Join our customer integrations team maintaining developer pipelines and API webhooks. Build user dashboard features with PHP, React, and Laravel.',
    requirements: [
      '1+ years of web application development experience',
      'Basic knowledge of PHP 8, MySQL relational queries, and React',
      'Committed worker eager to perform standard troubleshooting'
    ],
    salary: '$70k - $85k',
    location: 'Austin, TX',
    experienceLevel: 'Junior',
    workType: 'On-site',
    postedAt: '2026-05-23',
    category: 'Full Stack',
    featured: false
  }
];

export const INITIAL_DEVELOPERS: Developer[] = [
  {
    id: 'dev-1',
    fullName: 'Abhinav Kumar',
    email: 'abhinavkumark70@gmail.com',
    phone: '+1 (415) 880-9900',
    title: 'Lead Full Stack Architect',
    experience: '8 Years',
    techStack: ['TypeScript', 'React', 'Node.js', 'Express', 'MySQL', 'Firebase', 'PHP'],
    portfolioUrl: 'https://abhinav.dev',
    profileImage: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop',
    bio: 'Dedicated software architect specialized in full-stack cloud ecosystems, secure session persistence, and highly optimized SQL querying systems. Former Stripe developer.',
    verified: true,
    location: 'San Francisco, CA'
  },
  {
    id: 'dev-2',
    fullName: 'Abhinav Shrivastava',
    email: 'abhinavshrivastava09800@gmail.com',
    phone: '+1 (234) 567-8901',
    title: 'Senior Solutions Engineer',
    experience: '6 Years',
    techStack: ['Node.js', 'React', 'MongoDB', 'PostgreSQL', 'Docker', 'AWS'],
    portfolioUrl: 'https://solutions.dev/shrivastava',
    profileImage: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=200&auto=format&fit=crop',
    bio: 'Fascinated by high-throughput backend services and robust continuous deployment pipelines. Specialized in secure system integrations, Firebase triggers, and microservice mesh configurations.',
    verified: true,
    location: 'Austin, TX'
  },
  {
    id: 'dev-3',
    fullName: 'Sarah Chen',
    email: 'sarah.chen@devhire.net',
    phone: '+1 (650) 222-3838',
    title: 'Frontend Craft Expert',
    experience: '5 Years',
    techStack: ['React', 'TypeScript', 'Tailwind CSS', 'Framer Motion', 'Vue', 'GraphQL'],
    portfolioUrl: 'https://sarahchen.design',
    profileImage: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?q=80&w=200&auto=format&fit=crop',
    bio: 'Detail-oriented product development specialist focused on UI crafting, fluid responsive layouts, browser rendering pathways, and high-performance layout states.',
    verified: true,
    location: 'Boston, MA'
  },
  {
    id: 'dev-4',
    fullName: 'Liam O\'Connor',
    email: 'liam.oconnor@hire.io',
    phone: '+1 (512) 345-6789',
    title: 'Backend Systems Developer',
    experience: '3 Years',
    techStack: ['Node.js', 'Express', 'MySQL', 'Redis', 'Python', 'Docker'],
    portfolioUrl: 'https://liamdev.io',
    profileImage: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop',
    bio: 'Performance-driven software developer dedicated to clean API architectures, index-optimized SQL design, and secure authentication workflows.',
    verified: false,
    location: 'Seattle, WA'
  }
];

export const INITIAL_APPLICATIONS: Application[] = [
  {
    id: 'app-1',
    jobId: 'job-1',
    jobTitle: 'Senior Full Stack Developer',
    companyName: 'Stripe',
    fullName: 'Abhinav Kumar',
    email: 'abhinavkumark70@gmail.com',
    phone: '+1 (415) 880-9900',
    experience: '8 Years',
    techStack: ['TypeScript', 'React', 'Node.js', 'Express', 'MySQL', 'Firebase', 'PHP'],
    portfolioUrl: 'https://abhinav.dev',
    resumeName: 'Abhinav_Kumar_Resume.pdf',
    coverLetter: 'I would love to help Stripe engineer robust and secure global transaction management layers for SaaS startups.',
    status: 'Approved',
    createdAt: '2026-05-28'
  },
  {
    id: 'app-2',
    jobId: 'job-3',
    jobTitle: 'NodeJS Backend Architect',
    companyName: 'Vercel',
    fullName: 'Abhinav Shrivastava',
    email: 'abhinavshrivastava09800@gmail.com',
    phone: '+1 (234) 567-8901',
    experience: '6 Years',
    techStack: ['Node.js', 'React', 'MongoDB', 'PostgreSQL', 'Docker', 'AWS'],
    portfolioUrl: 'https://solutions.dev/shrivastava',
    resumeName: 'Shrivastava_Solutions_Architect.pdf',
    coverLetter: 'Vercel represents the premium standard in frontend orchestration. I have designed custom execution environments and believe my Node.js engine proficiency will match perfectly.',
    status: 'Pending',
    createdAt: '2026-05-29'
  },
  {
    id: 'app-3',
    jobId: 'job-2',
    jobTitle: 'Lead Frontend Engineer',
    companyName: 'Notion',
    fullName: 'Sarah Chen',
    email: 'sarah.chen@devhire.net',
    phone: '+1 (650) 222-3838',
    experience: '5 Years',
    techStack: ['React', 'TypeScript', 'Tailwind CSS', 'Framer Motion', 'Vue', 'GraphQL'],
    portfolioUrl: 'https://sarahchen.design',
    resumeName: 'Sarah_Chen_Portfolio.pdf',
    coverLetter: 'I am excited about interactive collaborative technologies. I can bring advanced interactive performance optimizations to your block layout system.',
    status: 'Pending',
    createdAt: '2026-05-29'
  },
  {
    id: 'app-4',
    jobId: 'job-4',
    jobTitle: 'Full Stack Developer',
    companyName: 'Clerk Inc.',
    fullName: 'Liam O\'Connor',
    email: 'liam.oconnor@hire.io',
    phone: '+1 (512) 345-6789',
    experience: '3 Years',
    techStack: ['Node.js', 'Express', 'MySQL', 'Redis', 'Python', 'Docker'],
    portfolioUrl: 'https://liamdev.io',
    resumeName: 'Liam_Resume_2026.pdf',
    coverLetter: 'Applying for the Mid level Full Stack opening. Experienced with secure login and backend validations.',
    status: 'Rejected',
    createdAt: '2026-05-24'
  }
];

export const INITIAL_TESTIMONIALS: Testimonial[] = [
  {
    id: 't-1',
    name: 'Alexandra Vance',
    role: 'VP of Engineering',
    company: 'Stripe',
    text: 'DevHire connected us with elite React & MySQL experts in under 48 hours. The verification badge program saved our engineering team weeks of technical screening.',
    rating: 5,
    image: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=200&auto=format&fit=crop'
  },
  {
    id: 't-2',
    name: 'Marc Levinson',
    role: 'Co-Founder & CTO',
    company: 'Clerk',
    text: 'Our verification workflows require developers who understand secure cookie bounds and session tokens. The developers we acquired here are truly elite.',
    rating: 5,
    image: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=200&auto=format&fit=crop'
  },
  {
    id: 't-3',
    name: 'Tariq Al-Sabah',
    role: 'Lead Recruiter',
    company: 'Vercel',
    text: 'A absolute masterpiece of hiring efficiency. The filter capabilities, smart stack matching, and elegant dashboard allows our managers to review portfolios in fractions of a second.',
    rating: 5,
    image: 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=200&auto=format&fit=crop'
  }
];
