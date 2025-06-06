<?php
use PHPUnit\Framework\TestCase;

class JellyfinNowPlayingTest extends TestCase
{
    public function test_default_now_playing_output()
    {
        $result = fetch_jellyfin_now_playing();
        $this->assertSame('The webmaster is not listening to music right now.', $result);
    }
}
