# PDCurses Zig

A Zig wrapper around [PDCurses Mod for SDL2](https://github.com/wudeng/pdcurses), providing terminal/curses functionality through SDL2 rendering.

This project exposes PDCurses as a Zig library via `@cImport`, giving you access to the full curses API from Zig code. It includes the PDCurses source (built as a static C library), example programs, a BDF font parser, and a CP437→UTF-8 converter.

## Overview

**PDCurses Mod for SDL2** is one of many PDCurses "ports" — each port renders the curses display using a different underlying platform library. This project uses the **SDL2 graphics backend**, meaning it creates an SDL2 window and renders text using SDL2's software or hardware-accelerated 2D API (via `SDL_Renderer`).

The PDCurses library implements a large portion of the POSIX `curses` API (as defined by X/Open), including `initscr`, `cbreak`, `raw`, `noecho`, `keypad`, `newwin`, `subwin`, `mvwin`, `mvderwin`, `getch`, `addstr`, `addch`, `box`, `border`, `hline`, `vline`, `refresh`, `wrefresh`, color support, soft labels, ripoffline lines, and a comprehensive set of character attributes.

## Project Structure

```
.
├── src/
│   ├── main.zig              # Zig API wrapper around PDCurses (curses functions + helpers)
│   ├── root.zig              # Zig package root (re-exports main.zig)
│   ├── pdcurses/
│   │   ├── pdcurses/         # PDCurses Mod for SDL2 C source code
│   │   │   ├── curses.h      # Main PDCurses header (POSIX-like terminal API)
│   │   │   ├── curspriv.h    # Internal PDCurses header
│   │   │   ├── panel.h       # Panel library header
│   │   │   ├── term.h        # Terminfo compatibility
│   │   │   ├── sdl2/         # SDL2-specific rendering backend
│   │   │   │   ├── pdcsdl.h  # SDL2 PDCurses internal header
│   │   │   │   ├── pdcdisp.c # SDL2 display/rendering implementation
│   │   │   │   ├── pdcgetsc.c# SDL2 get screen dimensions
│   │   │   │   ├── pdckbd.c  # SDL2 keyboard input handling
│   │   │   │   ├── pdcscrn.c # SDL2 screen initialization
│   │   │   │   ├── pdcsetsc.c# SDL2 set screen dimensions
│   │   │   │   ├── pdcutil.c # SDL2 utility functions (napms, beep, etc.)
│   │   │   │   └── sdl2_pdcurses.cfg  # Build configuration for the upstream PDCurses project
│   │   │   ├── pdcurses/*.c  # Core PDCurses C source files (platform-independent)
│   │   │   └── demos/        # PDCurses demo programs (firework.c, newtest.c, etc.)
│   │   ├── build.zig         # Zig build script for compiling PDCurses as a static C library
│   │   └── pdc_build.zig     # Alternate/simplified build script (auto-detects SDL2 via pkg-config)
│   ├── utils/
│   │   ├── bdf_font.zig      # BDF (Bitmap Distribution Format) font file parser
│   │   └── cp437.zig         # CP437 (DOS codepage) to UTF-8 converter
│   ├── example/
│   │   ├── simple_sdl.zig    # Minimal SDL2 window example (no PDCurses, just raw SDL2)
│   │   └── example.zig       # Full PDCurses example (forked from upstream PDCurses demos)
│   ├── tcc/                  # Bundled Tiny C Compiler (Windows)
│   └── tcc.zig               # Zig build helper for using TCC as the C compiler
├── build.zig                 # Top-level Zig build script (compiles examples + library)
├── Dockerfile                # Docker build for Linux (Ubuntu, with SDL2 + libGL)
├── .vscode/
│   ├── launch.json           # VS Code debugger configurations (debug/release, LLDB/GDB)
│   └── tasks.json            # VS Code build tasks (debug/release)
├── .gitmodules               # Git submodule configuration (imgui_sdl marked as added)
└── LICENSE                   # Unlicense (Public Domain)
```

## Building

### Prerequisites

- **Zig** (version compatible with the build.zig API — likely Zig 0.11+)
- **SDL2 development libraries** — The build uses either:
  - `sdl2-config --cflags --libs` (Linux/macOS), or
  - `pkg-config --cflags sdl2 --libs sdl2` (fallback)

On Ubuntu/Debian:
```bash
sudo apt-get install libsdl2-dev
```

On macOS with Homebrew:
```bash
brew install sdl2
```

On Windows, you may need to set up SDL2 paths manually or use the bundled TCC compiler path configuration.

### Build Commands

**Build all (default):**
```bash
zig build
```

**Run the full example (PDCurses demo):**
```bash
zig build run
```

**Run the simple SDL2 example:**
```bash
zig build run-simple-sdl
```

### Build Options

| Option | Default | Description |
|--------|---------|-------------|
| `target` | (host) | Target architecture/platform (e.g., `x86_64-windows-gnu`, `x86_64-linux-gnu`) |
| `tcc` | `false` | Use the bundled Tiny C Compiler instead of the system C compiler |

**Cross-compile example:**
```bash
zig build -Dtarget=x86_64-windows-gnu
```

**Build using TCC:**
```bash
zig build -Dtcc=true
```

### Docker (Linux)

A Dockerfile is provided that sets up an Ubuntu environment with SDL2, Zig, and libGL:

```bash
docker build -t pdcurses-zig .
docker run -it --rm -e DISPLAY=$DISPLAY -v /tmp/.X11-unix:/tmp/.X11-unix pdcurses-zig
```

### VS Code Integration

The project includes VS Code configurations in `.vscode/`:

**Build tasks** (via `tasks.json`):
- `zigBuild` — Debug build (default build task, `Ctrl+Shift+B`)
- `zigReleaseSafe` — Release-safe build

**Debug configurations** (via `launch.json`):
- `Zig Debug Run` — Build debug and run with CodeLLDB
- `Zig Debug (Windows)` — Windows-specific debug with GDB/LLDB
- `Zig Release Run` — Build release-safe and run with CodeLLDB
- `Attach` — Attach debugger to a running process

## Architecture

### PDCurses C Library (static)

The PDCurses C source lives in `src/pdcurses/` and is compiled as a static library (`libpdcurses.a` / `pdcurses.lib`) using Zig's C compilation capabilities. The build configuration is in `src/pdcurses/build.zig`.

Key aspects of the C build:
- **C standard:** C17 (`-std=c17`)
- **Platform define:** `PDC_WIDE` (enables wide/Unicode character support)
- **SDL2 integration:** The `HAVE_SDL2` define activates the SDL2 rendering backend
- **Source modules:** Display, keyboard input, screen management, color, terminfo compatibility, soft labels, scrolling, border/box drawing, cursor management, and the SDL2-specific rendering layer (`sdl2/pdcdisp.c`, `sdl2/pdckbd.c`, etc.)

### Zig Wrapper (`src/main.zig`)

The Zig wrapper uses `@cImport` to import the PDCurses C headers (`curses.h`, `panel.h`, `term.h`) and re-exports them as the Zig API. This gives Zig code direct access to:

- **Window management:** `initscr`, `endwin`, `newwin`, `delwin`, `subwin`, `mvwin`, `mvderwin`, `wresize`
- **Input:** `getch`, `wgetch`, `mvgetch`, `mvwgetch`, `ungetch`, `has_key`, `keypad`, `cbreak`, `raw`, `echo`, `noecho`
- **Output:** `addstr`, `addch`, `printw`, `waddstr`, `waddch`, `box`, `border`, `hline`, `vline`
- **Attributes & color:** `attron`, `attroff`, `attrset`, `COLOR_PAIR`, `init_pair`, `init_color`
- **Cursor:** `move`, `wmove`, `curs_set`
- **Refresh:** `refresh`, `wrefresh`, `doupdate`
- **Misc:** `napms`, `beep`, `flash`, `resize_term`, `use_default_colors`

Additionally, the wrapper provides Zig helper functions for attributes:
- `getCharAttr(ch, color_pair)` — Build a `chtype` with character + color pair + attributes
- `getWideCharAttr(ch, color_pair)` — Build a `cchar_t` with wide character + color pair + attributes

### Utilities

**BDF Font Parser** (`src/utils/bdf_font.zig`):
Parses BDF (Bitmap Distribution Format) font files, a text-based bitmap font format commonly used on X Window System. Useful for loading custom bitmap fonts for rendering.

**CP437 Converter** (`src/utils/cp437.zig`):
Converts CP437-encoded byte sequences (the classic DOS/IBM PC codepage) to UTF-8. This is useful when working with legacy text-mode applications or data that uses the CP437 character set, which includes box-drawing characters (╔═╗║╚╝), mathematical symbols, and other graphical characters.

### Examples

**Simple SDL2** (`src/example/simple_sdl.zig`):
A minimal SDL2 program that opens a window and draws colored rectangles using SDL2 directly (no PDCurses). Useful as a baseline SDL2 test.

**PDCurses Example** (`src/example/example.zig`):
A comprehensive PDCurses test program (forked from upstream PDCurses demos) that exercises the curses API — windows, colors, input handling, character display, attributes, and more.

## API Reference

The PDCurses API exposed through Zig mirrors the C curses API. Key functions include:

### Initialization & Cleanup
| Function | Description |
|----------|-------------|
| `initscr()` | Initialize the terminal; returns `WINDOW*` (stdscr) |
| `endwin()` | Restore terminal to original state |
| `isendwin()` | Check if `endwin()` has been called |
| `resize_term(nlines, ncols)` | Resize the terminal |
| `set_tabsize(size)` | Set tab size |
| `curses_version()` | Get PDCurses version string |

### Window Management
| Function | Description |
|----------|-------------|
| `newwin(nlines, ncols, begin_y, begin_x)` | Create a new window |
| `delwin(win)` | Delete a window |
| `mvwin(win, y, x)` | Move a window |
| `subwin(...)` / `derwin(...)` | Create a subwindow |
| `mvderwin(win, y, x)` | Move a subwindow within parent |
| `wresize(win, lines, cols)` | Resize a window |
| `dupwin(win)` | Duplicate a window |
| `wgetparent(win)` | Get parent window |
| `wgetscrreg(win, top, bot)` | Get scroll region |

### Input
| Function | Description |
|----------|-------------|
| `getch()` / `wgetch(win)` | Get a character from keyboard |
| `mvgetch(y, x)` / `mvwgetch(win, y, x)` | Move and get a character |
| `ungetch(ch)` | Push a character back to the input queue |
| `has_key(ch)` | Check if terminal recognizes a key |
| `keypad(win, bf)` | Enable/disable keypad translation |
| `cbreak()` / `nocbreak()` | Enable/disable cbreak mode |
| `raw()` / `noraw()` | Enable/disable raw mode |
| `echo()` / `noecho()` | Enable/disable echo |
| `typeahead(fd)` | Specify typeahead file descriptor |
| `PDC_save_key_modifiers(flag)` | Save key modifiers |
| `PDC_get_key_modifiers()` | Get key modifiers |

### Output
| Function | Description |
|----------|-------------|
| `addstr(str)` / `waddstr(win, str)` | Add a string to the window |
| `addch(ch)` / `waddch(win, ch)` | Add a character to the window |
| `box(win, verch, horch)` | Draw a box around a window |
| `border(ls, rs, ts, bs, tl, tr, bl, br)` | Draw borders |
| `hline(ch, n)` / `vline(ch, n)` | Draw horizontal/vertical lines |
| `clear()` / `wclear(win)` | Clear window |
| `erase()` / `werase(win)` | Erase window |
| `clrtobot()` / `wclrtobot(win)` | Clear to bottom of window |
| `clrtoeol()` / `wclrtoeol(win)` | Clear to end of line |
| `printw(fmt, ...)` / `wprintw(win, fmt, ...)` | Print formatted output |
| `refresh()` / `wrefresh(win)` | Update terminal display |
| `doupdate()` | Update all windows |
| `redrawwin(win)` / `redrawln(win, ...)` | Redraw window or lines |
| `wnoutrefresh(win)` | Copy window to virtual screen |

### Cursor & Position
| Function | Description |
|----------|-------------|
| `move(y, x)` / `wmove(win, y, x)` | Move cursor |
| `getcury(win)` / `getcurx(win)` | Get cursor position |
| `getbegy(win)` / `getbegx(win)` | Get window beginning position |
| `getmaxy(win)` / `getmaxx(win)` | Get window dimensions |
| `getpary(win)` / `getparx(win)` | Get subwindow parent origin |
| `curs_set(visibility)` | Set cursor visibility (0=hidden, 1=normal, 2=high) |

### Attributes & Color
| Function | Description |
|----------|-------------|
| `attron(attrs)` / `wattron(win, attrs)` | Turn on attributes |
| `attroff(attrs)` / `wattroff(win, attrs)` | Turn off attributes |
| `attrset(attrs)` / `wattrset(win, attrs)` | Set attributes |
| `color_content(color, r, g, b)` | Get RGB values of a color |
| `has_colors()` | Check if terminal supports color |
| `init_color(color, r, g, b)` | Define a custom color |
| `init_pair(pair, fg, bg)` | Define a color pair |
| `pair_content(pair, fg, bg)` | Get color pair contents |
| `COLOR_PAIR(n)` | Get attribute for color pair n |
| `use_default_colors()` | Use default terminal colors |
| `assume_default_colors(fg, bg)` | Set default colors |

### Misc
| Function | Description |
|----------|-------------|
| `napms(ms)` | Sleep for milliseconds |
| `beep()` | Sound the terminal bell |
| `flash()` | Flash the screen |
| `use_env(f)` | Control environment variable usage |
| `putwin(win, filep)` / `getwin(filep)` | Save/restore window to/from file |
| `filter()` / `nofilter()` | Line-mode filtering |
| `ripoffline(line, init)` | Reserve screen line |
| `slk_*(...)` | Soft label key functions |
| `PDC_set_title(title)` | Set window title (SDL2 window) |
| `PDC_set_blink(on)` | Set blink attribute behavior |
| `PDC_set_bold(on)` | Set bold attribute behavior |

### Zig Helper Functions
| Function | Description |
|----------|-------------|
| `getCharAttr(ch: u8, color_pair: u32) chtype` | Build character with color pair and attributes |
| `getWideCharAttr(ch: u32, color_pair: u32) cchar_t` | Build wide character with color pair and attributes |

## License

This project is released under the [Unlicense](LICENSE) — it is in the **public domain**.

You are free to copy, modify, publish, use, compile, sell, or distribute this software, either in source code form or as a compiled binary, for any purpose, commercial or non-commercial, and by any means.

## Acknowledgments

- [PDCurses](https://pdcurses.org/) — The original public domain curses library
- [PDCurses Mod for SDL2](https://github.com/wudeng/pdcurses) — The SDL2 port used in this project
- [SDL2](https://www.libsdl.org/) — Simple DirectMedia Layer