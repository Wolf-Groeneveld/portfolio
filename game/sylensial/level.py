import pygame

from .config import (
    TILE,
    GRID_COLS,
    GRID_ROWS,
    FIELD_WIDTH,
    SCREEN_HEIGHT,
    COLOR_GRASS_A,
    COLOR_GRASS_B,
    COLOR_GRID_LINE,
    COLOR_PATH,
    COLOR_PATH_DARK,
    COLOR_PATH_EDGE,
    COLOR_BASE,
    COLOR_BASE_CORE,
)

WAYPOINT_CELLS = [
    (-1, 1),
    (16, 1),
    (16, 5),
    (3, 5),
    (3, 9),
    (16, 9),
    (16, 13),
    (4, 13),
]


def cell_center(col, row):
    return (col * TILE + TILE // 2, row * TILE + TILE // 2)


def trace_path_cells(waypoints):
    cells = set()
    for (c1, r1), (c2, r2) in zip(waypoints, waypoints[1:]):
        if c1 == c2:
            step = 1 if r2 >= r1 else -1
            for row in range(r1, r2 + step, step):
                cells.add((c1, row))
        else:
            step = 1 if c2 >= c1 else -1
            for col in range(c1, c2 + step, step):
                cells.add((col, r1))
    return cells


class Level:
    def __init__(self):
        self.points = [pygame.Vector2(cell_center(c, r)) for c, r in WAYPOINT_CELLS]
        self.path_cells = trace_path_cells(WAYPOINT_CELLS)
        self.base_cell = WAYPOINT_CELLS[-1]
        self.surface = None
        self._build_surface()

    def pixel_to_cell(self, x, y):
        return (x // TILE, y // TILE)

    def in_field(self, x, y):
        return 0 <= x < FIELD_WIDTH and 0 <= y < SCREEN_HEIGHT

    def is_buildable(self, cell):
        col, row = cell
        if not (0 <= col < GRID_COLS and 0 <= row < GRID_ROWS):
            return False
        if cell in self.path_cells:
            return False
        return True

    def _build_surface(self):
        surface = pygame.Surface((FIELD_WIDTH, SCREEN_HEIGHT))
        for row in range(GRID_ROWS):
            for col in range(GRID_COLS):
                shade = COLOR_GRASS_A if (col + row) % 2 == 0 else COLOR_GRASS_B
                rect = pygame.Rect(col * TILE, row * TILE, TILE, TILE)
                surface.fill(shade, rect)

        for col in range(GRID_COLS + 1):
            pygame.draw.line(
                surface, COLOR_GRID_LINE, (col * TILE, 0), (col * TILE, SCREEN_HEIGHT)
            )
        for row in range(GRID_ROWS + 1):
            pygame.draw.line(
                surface, COLOR_GRID_LINE, (0, row * TILE), (FIELD_WIDTH, row * TILE)
            )

        edge_pts = [(int(p.x), int(p.y)) for p in self.points]
        pygame.draw.lines(surface, COLOR_PATH_EDGE, False, edge_pts, TILE)
        pygame.draw.lines(surface, COLOR_PATH, False, edge_pts, TILE - 8)
        for point in self.points:
            pygame.draw.circle(surface, COLOR_PATH, (int(point.x), int(point.y)), (TILE - 8) // 2)

        for i in range(len(edge_pts) - 1):
            start = pygame.Vector2(edge_pts[i])
            end = pygame.Vector2(edge_pts[i + 1])
            length = (end - start).length()
            if length == 0:
                continue
            direction = (end - start) / length
            travelled = 12
            while travelled < length - 12:
                a = start + direction * travelled
                b = start + direction * min(travelled + 10, length - 12)
                pygame.draw.line(
                    surface, COLOR_PATH_DARK, (int(a.x), int(a.y)), (int(b.x), int(b.y)), 3
                )
                travelled += 26

        self._draw_base(surface)
        self.surface = surface

    def _draw_base(self, surface):
        cx, cy = cell_center(*self.base_cell)
        pygame.draw.circle(surface, (8, 14, 22), (cx, cy), 30)
        pygame.draw.circle(surface, COLOR_BASE, (cx, cy), 26, 3)
        pygame.draw.circle(surface, COLOR_BASE, (cx, cy), 18, 2)
        pygame.draw.circle(surface, COLOR_BASE_CORE, (cx, cy), 8)
        for angle in range(0, 360, 45):
            tip = pygame.Vector2(1, 0).rotate(angle) * 26
            pygame.draw.line(
                surface,
                COLOR_BASE,
                (cx, cy),
                (int(cx + tip.x), int(cy + tip.y)),
                2,
            )

    def draw(self, surface):
        surface.blit(self.surface, (0, 0))
