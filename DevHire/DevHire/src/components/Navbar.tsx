import React, { useState } from 'react';
import { Menu, X, User, LogOut, Shield, Briefcase, Users, LayoutDashboard, Settings } from 'lucide-react';

interface NavbarProps {
  currentPage: string;
  onNavigate: (page: string) => void;
  currentUser: { name: string; email: string; role: 'developer' | 'company' | 'admin' } | null;
  onLogout: () => void;
  onSetRole: (role: 'developer' | 'company' | 'admin' | null) => void;
}

export const Navbar: React.FC<NavbarProps> = ({
  currentPage,
  onNavigate,
  currentUser,
  onLogout,
  onSetRole
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [showProfileDropdown, setShowProfileDropdown] = useState(false);

  const menuItems = [
    { id: 'home', label: 'Home' },
    { id: 'jobs', label: 'Jobs' },
    { id: 'developers', label: 'Developers' },
    { id: 'how-it-works', label: 'How It Works' },
    { id: 'pricing', label: 'Pricing' },
    { id: 'contact', label: 'Contact' }
  ];

  const handleNavClick = (pageId: string) => {
    onNavigate(pageId);
    setIsOpen(false);
    setShowProfileDropdown(false);
  };

  return (
    <nav className="sticky top-0 z-50 w-full glass-panel border-b border-white/6 bg-slate-950/80 backdrop-blur-md">
      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between gap-10">
          
          {/* LEFT: Logo */}
          <div className="flex flex-shrink-0 items-center">
            <button 
              onClick={() => handleNavClick('home')}
              className="flex items-center gap-2.5 text-xl font-display font-bold tracking-tight text-white hover:opacity-90 transition-all cursor-pointer"
            >
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-tr from-brand-primary to-brand-secondary text-white font-display font-black text-lg">
                DH
              </div>
              <span className="bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent">
                DevHire
              </span>
            </button>
          </div>

          {/* CENTER: Navigation Links (2.5rem / 40px Gap matching SaaS guidelines) */}
          <div className="hidden md:flex items-center justify-center flex-1">
            <ul className="flex items-center gap-10">
              {menuItems.map((item) => (
                <li key={item.id}>
                  <button
                    onClick={() => handleNavClick(item.id)}
                    className={`relative text-[0.95rem] font-medium transition-all duration-200 cursor-pointer py-1.5 px-1 ${
                      currentPage === item.id
                        ? 'text-white'
                        : 'text-slate-400 hover:text-white'
                    }`}
                  >
                    {item.label}
                    {currentPage === item.id && (
                      <span className="absolute bottom-0 left-0 h-0.5 w-full bg-gradient-to-r from-brand-primary to-brand-secondary rounded-full" />
                    )}
                  </button>
                </li>
              ))}
            </ul>
          </div>

          {/* RIGHT: User Profile and Role Switchers */}
          <div className="hidden md:flex items-center justify-end gap-4 flex-shrink-0">
            {currentUser ? (
              <div className="relative">
                <button
                  onClick={() => setShowProfileDropdown(!showProfileDropdown)}
                  className="flex items-center gap-2.5 px-3 py-1.5 rounded-full border border-slate-800 bg-slate-900/80 hover:bg-slate-800/80 transition-all text-sm text-slate-300 font-medium hover:text-white cursor-pointer"
                >
                  <div className="h-6 w-6 rounded-full bg-brand-primary/20 flex items-center justify-center text-brand-primary text-xs font-bold ring-1 ring-brand-primary/30">
                    {currentUser.role === 'admin' ? <Shield className="h-3 w-3" /> : currentUser.name.charAt(0)}
                  </div>
                  <span>{currentUser.name}</span>
                  <span className="text-[10px] uppercase tracking-wider bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded border border-slate-700">
                    {currentUser.role}
                  </span>
                </button>

                {showProfileDropdown && (
                  <div className="absolute right-0 mt-2 w-56 rounded-xl border border-slate-800 bg-slate-950 p-1.5 shadow-2xl z-50">
                    <div className="px-3 py-2 text-xs border-b border-slate-900 text-slate-400">
                      Signed in as <b className="text-slate-200">{currentUser.email}</b>
                    </div>
                    
                    {currentUser.role === 'admin' && (
                      <button
                        onClick={() => handleNavClick('admin')}
                        className="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-white transition-all rounded-lg mt-1 cursor-pointer"
                      >
                        <LayoutDashboard className="h-4 w-4 text-brand-primary" />
                        Admin Dashboard
                      </button>
                    )}

                    {currentUser.role === 'company' && (
                      <button
                        onClick={() => handleNavClick('company')}
                        className="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-white transition-all rounded-lg mt-1 cursor-pointer"
                      >
                        <Briefcase className="h-4 w-4 text-brand-secondary" />
                        Company Panel
                      </button>
                    )}

                    {currentUser.role === 'developer' && (
                      <button
                        onClick={() => handleNavClick('developers')}
                        className="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 hover:text-white transition-all rounded-lg mt-1 cursor-pointer"
                      >
                        <Users className="h-4 w-4 text-indigo-400" />
                        My Public Profile
                      </button>
                    )}

                    <div className="h-px bg-slate-900 my-1" />
                    
                    {/* Fast role switcher for easy evaluation */}
                    <div className="px-3 py-1.5 text-[10px] text-slate-500 font-mono tracking-wider">
                      DEV QUICK-SWITCH:
                    </div>
                    <div className="grid grid-cols-3 gap-1 px-1.5 pb-1.5">
                      <button
                        onClick={() => onSetRole('developer')}
                        className={`text-[9px] font-semibold py-1 rounded transition-all cursor-pointer ${
                          currentUser.role === 'developer'
                            ? 'bg-indigo-600/30 text-indigo-400 border border-indigo-500/30'
                            : 'bg-slate-900 hover:bg-slate-800 text-slate-400'
                        }`}
                      >
                        Dev
                      </button>
                      <button
                        onClick={() => onSetRole('company')}
                        className={`text-[9px] font-semibold py-1 rounded transition-all cursor-pointer ${
                          currentUser.role === 'company'
                            ? 'bg-cyan-600/30 text-cyan-400 border border-cyan-500/30'
                            : 'bg-slate-900 hover:bg-slate-800 text-slate-400'
                        }`}
                      >
                        Comp
                      </button>
                      <button
                        onClick={() => onSetRole('admin')}
                        className={`text-[9px] font-semibold py-1 rounded transition-all cursor-pointer ${
                          currentUser.role === 'admin'
                            ? 'bg-violet-600/30 text-violet-400 border border-violet-500/30'
                            : 'bg-slate-900 hover:bg-slate-800 text-slate-400'
                        }`}
                      >
                        Admin
                      </button>
                    </div>

                    <div className="h-px bg-slate-900 my-1" />

                    <button
                      onClick={onLogout}
                      className="flex w-full items-center gap-2 px-3 py-2 text-sm text-brand-accent hover:bg-brand-accent/10 transition-all rounded-lg cursor-pointer"
                    >
                      <LogOut className="h-4 w-4" />
                      Sign Out
                    </button>
                  </div>
                )}
              </div>
            ) : (
              <div className="flex items-center gap-3">
                <button
                  onClick={() => handleNavClick('login')}
                  className="text-sm font-medium text-slate-400 hover:text-white transition-all cursor-pointer"
                >
                  Sign In
                </button>
                <button
                  onClick={() => handleNavClick('register')}
                  className="rounded-full bg-gradient-to-r from-brand-primary to-brand-secondary hover:opacity-95 text-white text-xs font-semibold px-4 py-2 shadow-lg shadow-brand-primary/25 cursor-pointer hover:shadow-brand-primary/40 transition-all"
                >
                  Create Account
                </button>
              </div>
            )}
          </div>

          {/* Hamburger Menu Mobile */}
          <div className="flex md:hidden">
            <button
              onClick={() => setIsOpen(!isOpen)}
              className="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-900 focus:outline-none transition-all cursor-pointer"
            >
              {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
            </button>
          </div>

        </div>
      </div>

      {/* Mobile Menu Overlay */}
      {isOpen && (
        <div className="md:hidden border-t border-slate-900 bg-slate-950 p-4 space-y-3 shadow-2xl animate-in fade-in slide-in-from-top-4 duration-300">
          <ul className="space-y-1">
            {menuItems.map((item) => (
              <li key={item.id}>
                <button
                  onClick={() => handleNavClick(item.id)}
                  className={`flex w-full px-3 py-2 rounded-lg text-sm font-medium transition-all ${
                    currentPage === item.id
                      ? 'bg-slate-900 text-white border-l-2 border-brand-primary'
                      : 'text-slate-400 hover:bg-slate-900/50 hover:text-white'
                  }`}
                >
                  {item.label}
                </button>
              </li>
            ))}
          </ul>

          <div className="h-px bg-slate-900 my-2" />

          {currentUser ? (
            <div className="space-y-2">
              <div className="px-3 py-1 text-xs text-slate-500">
                Active: <b className="text-slate-300">{currentUser.email}</b>
              </div>
              {currentUser.role === 'admin' && (
                <button
                  onClick={() => handleNavClick('admin')}
                  className="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 rounded-lg cursor-pointer"
                >
                  <LayoutDashboard className="h-4 w-4 text-brand-primary" />
                  Admin Dashboard
                </button>
              )}
              {currentUser.role === 'company' && (
                <button
                  onClick={() => handleNavClick('company')}
                  className="flex w-full items-center gap-2 px-3 py-2 text-sm text-slate-300 hover:bg-slate-900 rounded-lg cursor-pointer"
                >
                  <Briefcase className="h-4 w-4 text-brand-secondary" />
                  Company Panel
                </button>
              )}
              <button
                onClick={onLogout}
                className="flex w-full items-center gap-2 px-3 py-2 text-sm text-brand-accent hover:bg-brand-accent/10 rounded-lg cursor-pointer"
              >
                <LogOut className="h-4 w-4" />
                Sign Out
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-2 gap-2 p-2">
              <button
                onClick={() => handleNavClick('login')}
                className="flex items-center justify-center py-2 px-4 rounded-lg text-sm text-slate-300 border border-slate-900 hover:bg-slate-900 transition-all font-medium cursor-pointer"
              >
                Sign In
              </button>
              <button
                onClick={() => handleNavClick('register')}
                className="flex items-center justify-center py-2 px-4 rounded-lg text-sm text-white bg-brand-primary font-semibold hover:opacity-90 transition-all cursor-pointer"
              >
                Sign Up
              </button>
            </div>
          )}
        </div>
      )}
    </nav>
  );
};
