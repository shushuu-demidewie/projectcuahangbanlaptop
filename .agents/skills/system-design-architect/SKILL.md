---
name: system-design-architect
description: >-
  Use when designing large-scale distributed architectures, estimating throughput/storage/bandwidth (back-of-the-envelope calculations), evaluating caching and database replication/sharding strategies, applying the CAP theorem, or tackling system design interview problems based on The System Design Primer.
---

# System Design Architect

Distilled from [The System Design Primer](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/system-design-primer).

## When to Use This Skill
- Designing architectures for scalable web applications, microservices, or APIs.
- Back-of-the-envelope capacity estimations (QPS, IOPS, storage over 5 years, network bandwidth).
- Choosing appropriate databases (SQL vs NoSQL), caching layers (Redis/Memcached), and load balancing schemes.
- Solving system design challenges (URL shortener, Twitter timeline, Pastebin, Web crawler).

---

## 1. The 4-Step System Design Framework

### Step 1: Outline Use Cases, Scope & Constraints (3-5 mins)
- **Features in scope vs out of scope**: What are the 2-3 essential features? (e.g., Read-heavy vs Write-heavy ratio).
- **Scale Estimations (Back-of-the-envelope)**:
  - Daily Active Users (DAU): e.g., 100M DAU.
  - Queries Per Second (QPS): $\frac{100\text{M} \times 10\text{ actions}}{86,400\text{ seconds}} \approx 12,000\text{ QPS}$ (Peak $\approx 2\times\text{ to }3\times$).
  - Storage estimation: Average payload size $\times$ writes per day $\times$ 365 $\times$ 5 years.
  - Bandwidth: Incoming / outgoing bytes per second.

### Step 2: High-Level Design (5-10 mins)
- Draw or describe the end-to-end flow:
  `Client -> DNS/CDN -> Load Balancer (Nginx/HAProxy) -> API Gateway -> Application Servers -> Database`
- Establish the data model and key API endpoints (`REST` or `gRPC`).

### Step 3: Design Core Components & Identify Bottlenecks (15 mins)
- Where will the single point of failure (SPOF) be?
- Database read bottleneck $\implies$ Read replicas / Master-Slave replication.
- Repeated query bottleneck $\implies$ Distributed Cache (Redis) with Cache-Aside or Write-Through.
- High write volume bottleneck $\implies$ Sharding (horizontal partitioning) by consistent hashing, or Message Queues (Kafka/RabbitMQ) for asynchronous processing.

### Step 4: Scale the Design & Address Trade-offs (10 mins)
- **CAP Theorem Selection**:
  - **CP (Consistency + Partition Tolerance)**: Banking, checkout, inventory management.
  - **AP (Availability + Partition Tolerance)**: Social feeds, comments, notifications.
- **Failover & High Availability**: Active-Passive vs Active-Active clustering.
- **Security & Rate Limiting**: Token bucket / Leaky bucket algorithm at API gateway.

---

## 2. Scalability Numbers Every Engineer Should Know

- L1 cache reference: ~0.5 ns
- Main memory (RAM) reference: ~100 ns
- Read 1 MB sequentially from memory: ~250 µs
- Read 1 MB sequentially from SSD: ~1 ms
- Send 1 MB over 1 Gbps network: ~10 ms
- Read 1 MB sequentially from HDD: ~20 ms
- Cross-continental round trip: ~150 ms

---

## 3. Architecture Component Checklist

1. **Load Balancing**: Layer 4 (TCP/UDP) vs Layer 7 (HTTP/HTTPS, path-based routing).
2. **Caching**:
   - Cache patterns: Cache-Aside, Write-Through, Write-Behind, Refresh-Ahead.
   - Eviction policies: LRU (Least Recently Used), LFU, FIFO.
3. **Database Scaling**:
   - Master-Slave replication (read scalability).
   - Sharding / Partitioning (write scalability via consistent hashing).
   - Denormalization & NoSQL (MongoDB, DynamoDB, Cassandra) for flexible schema & massive scale.
4. **Asynchronous Processing**:
   - Message Queues (RabbitMQ, Kafka, SQS) to decouple services and buffer traffic spikes.

---

## 4. Local Solutions & Reference Implementations
- System Design Solutions: [solutions/system_design/](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/system-design-primer/solutions/system_design)
- Object-Oriented Design Solutions: [solutions/object_oriented_design/](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/system-design-primer/solutions/object_oriented_design)
