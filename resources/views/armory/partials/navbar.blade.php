<nav class="arena-nav-wrapper">
    <div class="nav-container">
        <ul>
            <li>
                <a href="{{ route('armory') }}">
                    {{ __('armory.title') }}
                </a>
            </li>
            <li>
                <a href="{{ route('armory.arena-2v2') }}">
                    2v2
                </a>
            </li>
            <li>
                <a href="{{ route('armory.arena-3v3') }}">
                    3v3
                </a>
            </li>
            <li>
                <a href="{{ route('armory.arena-5v5') }}">
                    5v5
                </a>
            </li>
            <li>
                <a href="{{ route('armory.solo-pvp') }}">
                    {{ __('armory.solo_pvp_title') }}
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.arena-nav-wrapper .nav-container {
    border: 2px double #4338ca;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.7);
    padding: 1.2rem;
    margin-bottom: 2rem;
}

.arena-nav-wrapper ul {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.arena-nav-wrapper li a {
    padding: 1rem 1.5rem;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
    font-size: 1.1rem;
    font-weight: bold;
    background: rgba(255, 255, 255, 0.1);
}

.arena-nav-wrapper li a:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}
</style>