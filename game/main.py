import asyncio

# Expliciete import is nodig voor pygbag: de web-loader scant main.py
# om te bepalen welke packages in de browser geladen moeten worden.
import pygame  # noqa: F401

from sylensial.game import Game


async def main():
    await Game().run()


asyncio.run(main())
