import pygame


class EnemyType:
    def __init__(self, name, hp, speed, reward, damage, radius, color, shape, is_boss=False):
        self.name = name
        self.hp = hp
        self.speed = speed
        self.reward = reward
        self.damage = damage
        self.radius = radius
        self.color = color
        self.shape = shape
        self.is_boss = is_boss


HUSK = EnemyType("Husk", 36, 60, 7, 1, 13, (158, 126, 100), "circle")
STRIDER = EnemyType("Strider", 22, 116, 6, 1, 11, (150, 198, 122), "triangle")
JUGGERNAUT = EnemyType("Juggernaut", 160, 40, 22, 2, 18, (182, 116, 156), "hex")
MORHAUNT = EnemyType("Morhaunt", 2600, 44, 340, 12, 30, (224, 92, 110), "boss", is_boss=True)


class Enemy:
    def __init__(self, path, kind):
        self.path = path
        self.kind = kind
        self.max_hp = kind.hp
        self.hp = float(kind.hp)
        self.base_speed = float(kind.speed)
        self.speed = float(kind.speed)
        self.reward = kind.reward
        self.damage = kind.damage
        self.radius = kind.radius
        self.color = kind.color
        self.pos = pygame.Vector2(path[0])
        self.heading = pygame.Vector2(1, 0)
        self.target_index = 1
        self.slow_timer = 0.0
        self.reached_end = False
        self.dead = False
        self.rewarded = False
        self.enraged = False
        self.flash = 0.0

    def apply_slow(self, factor, duration):
        self.speed = self.base_speed * factor
        self.slow_timer = max(self.slow_timer, duration)

    def take_damage(self, amount):
        self.hp -= amount
        self.flash = 0.08
        if self.hp <= 0:
            self.dead = True

    def progress_key(self, origin):
        return (self.target_index, -(self.pos - origin).length_squared())

    def update(self, dt):
        if self.flash > 0:
            self.flash -= dt
        if self.slow_timer > 0:
            self.slow_timer -= dt
            if self.slow_timer <= 0:
                self.speed = self.base_speed
        if self.kind.is_boss and not self.enraged and self.hp <= self.max_hp * 0.5:
            self.enraged = True
            self.base_speed *= 1.6
            if self.slow_timer <= 0:
                self.speed = self.base_speed

        travel = self.speed * dt
        while travel > 0 and not self.reached_end:
            target = self.path[self.target_index]
            offset = target - self.pos
            distance = offset.length()
            if distance == 0:
                self._advance_waypoint()
                continue
            self.heading = offset / distance
            if distance <= travel:
                self.pos = pygame.Vector2(target)
                travel -= distance
                self._advance_waypoint()
            else:
                self.pos += self.heading * travel
                travel = 0

    def _advance_waypoint(self):
        self.target_index += 1
        if self.target_index >= len(self.path):
            self.reached_end = True

    def draw(self, surface):
        center = (int(self.pos.x), int(self.pos.y))
        pygame.draw.ellipse(
            surface,
            (6, 8, 12),
            pygame.Rect(center[0] - self.radius, center[1] + self.radius - 5, self.radius * 2, 10),
        )
        body = self.color
        if self.flash > 0:
            body = (255, 255, 255)
        elif self.enraged:
            body = (255, 120, 110)

        if self.kind.shape == "triangle":
            self._draw_triangle(surface, center, body)
        elif self.kind.shape == "hex":
            self._draw_polygon(surface, center, body, 6, 0)
        elif self.kind.shape == "boss":
            self._draw_boss(surface, center, body)
        else:
            pygame.draw.circle(surface, body, center, self.radius)
            pygame.draw.circle(surface, (12, 14, 18), center, self.radius, 2)
            pygame.draw.circle(surface, (12, 14, 18), center, self.radius - 5, 1)

        if not self.kind.is_boss:
            self._draw_health_bar(surface, center)

    def _draw_triangle(self, surface, center, body):
        angle = self.heading.angle_to(pygame.Vector2(1, 0))
        points = []
        for offset in (0, 130, -130):
            vec = pygame.Vector2(self.radius, 0).rotate(-angle + offset)
            points.append((center[0] + vec.x, center[1] + vec.y))
        pygame.draw.polygon(surface, body, points)
        pygame.draw.polygon(surface, (12, 16, 14), points, 2)

    def _draw_polygon(self, surface, center, body, sides, rotation):
        points = []
        for i in range(sides):
            vec = pygame.Vector2(self.radius, 0).rotate(rotation + i * (360 / sides))
            points.append((center[0] + vec.x, center[1] + vec.y))
        pygame.draw.polygon(surface, body, points)
        pygame.draw.polygon(surface, (16, 12, 16), points, 2)

    def _draw_boss(self, surface, center, body):
        spikes = []
        for i in range(12):
            radius = self.radius + (10 if i % 2 == 0 else 0)
            vec = pygame.Vector2(radius, 0).rotate(i * 30)
            spikes.append((center[0] + vec.x, center[1] + vec.y))
        pygame.draw.polygon(surface, (40, 12, 18), spikes)
        pygame.draw.circle(surface, body, center, self.radius)
        pygame.draw.circle(surface, (28, 8, 14), center, self.radius, 4)
        pygame.draw.circle(surface, (255, 214, 120), center, 9)
        pygame.draw.circle(surface, (60, 14, 18), center, 4)

    def _draw_health_bar(self, surface, center):
        if self.hp >= self.max_hp:
            return
        width = self.radius * 2 + 6
        x = center[0] - width // 2
        y = center[1] - self.radius - 10
        ratio = max(0.0, self.hp / self.max_hp)
        pygame.draw.rect(surface, (20, 22, 28), pygame.Rect(x, y, width, 5))
        fill = (120, 210, 130) if ratio > 0.4 else (228, 170, 80)
        pygame.draw.rect(surface, fill, pygame.Rect(x, y, int(width * ratio), 5))
