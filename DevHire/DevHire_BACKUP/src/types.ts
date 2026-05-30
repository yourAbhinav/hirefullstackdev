export interface Job {
  id: string;
  title: string;
  companyName: string;
  companyLogo?: string;
  description: string;
  requirements: string[];
  salary: string;
  location: string;
  experienceLevel: 'Junior' | 'Mid' | 'Senior';
  workType: 'Remote' | 'Hybrid' | 'On-site';
  postedAt: string;
  category: string;
  featured: boolean;
}

export interface Developer {
  id: string;
  fullName: string;
  email: string;
  phone: string;
  title: string;
  experience: string; // e.g. "5 Years"
  techStack: string[];
  portfolioUrl?: string;
  profileImage?: string;
  bio: string;
  verified: boolean;
  location: string;
}

export interface Application {
  id: string;
  jobId: string;
  jobTitle: string;
  companyName: string;
  fullName: string;
  email: string;
  phone: string;
  experience: string;
  techStack: string[];
  portfolioUrl?: string;
  resumeName: string;
  coverLetter?: string;
  status: 'Pending' | 'Approved' | 'Rejected';
  createdAt: string;
}

export interface Testimonial {
  id: string;
  name: string;
  role: string;
  company?: string;
  text: string;
  image?: string;
  rating: number;
}

export interface ContactMessage {
  id: string;
  name: string;
  email: string;
  subject: string;
  message: string;
  createdAt: string;
}
