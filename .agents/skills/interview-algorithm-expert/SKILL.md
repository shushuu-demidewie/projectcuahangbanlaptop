---
name: interview-algorithm-expert
description: >-
  Use when solving algorithm problems, analyzing Big-O time and space complexity, selecting optimal data structures (arrays, linked lists, trees, graphs, heaps, hash tables), or preparing for technical coding interviews based on Coding Interview University.
---

# Interview Algorithm & Data Structure Expert

Based on knowledge distilled from [Coding Interview University](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/coding-interview-university).

## When to Use This Skill
- The user is solving algorithmic challenges (LeetCode, HackerRank, interview questions).
- Evaluating time and space complexity ($O(1)$, $O(\log n)$, $O(n)$, $O(n \log n)$, $O(n^2)$).
- Choosing the right data structure for specific performance constraints.
- Reviewing CS fundamentals: sorting, searching, recursion, trees, graphs, dynamic programming.

---

## 1. Problem-Solving Protocol (5 Steps)

1. **Clarify Constraints & Input/Output**:
   - Determine input size $N$, expected data range, edge cases (empty input, null, negatives, duplicates).
   - Clarify time and memory budget (e.g., $N \le 10^5 \implies O(n)$ or $O(n \log n)$ required).
2. **Brute Force First**:
   - State the naive solution and its complexity (e.g., $O(n^2)$ via nested loop).
3. **Identify Bottlenecks & Optimize**:
   - Use Hash Table to trade space for time ($O(n^2) \to O(n)$).
   - Use Two Pointers / Sliding Window on sorted data ($O(n \log n) \to O(n)$).
   - Use Binary Search for ordered search space ($O(\log n)$).
   - Use Dynamic Programming when subproblems overlap and have optimal substructure.
4. **Walk Through with Concrete Examples**:
   - Dry-run step-by-step with small cases (e.g., array length 3, empty array, single element).
5. **Code & Complexity Summary**:
   - Write clean, readable code with descriptive variable names.
   - Always state: **Time Complexity** and **Space Complexity** with explanation.

---

## 2. Data Structure Quick Selector

| Data Structure | Access | Search | Insert | Delete | Best Use Case |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Array / Vector** | $O(1)$ | $O(n)$ | $O(n)$ | $O(n)$ | Fast random access by index, contiguous memory. |
| **Linked List** | $O(n)$ | $O(n)$ | $O(1)$* | $O(1)$* | Frequent insertions/deletions at known positions/head. |
| **Hash Table** | N/A | $O(1)$ avg | $O(1)$ avg | $O(1)$ avg | Key-value lookup, frequency counting, duplicate detection. |
| **Stack (LIFO)** | $O(n)$ | $O(n)$ | $O(1)$ | $O(1)$ | Bracket matching, recursion unwinding, monotonic stack. |
| **Queue (FIFO)** | $O(n)$ | $O(n)$ | $O(1)$ | $O(1)$ | BFS graph traversal, task scheduling, buffer stream. |
| **Binary Search Tree**| $O(\log n)$ | $O(\log n)$ | $O(\log n)$ | $O(\log n)$ | Sorted order maintenance, range queries ($O(n)$ worst if unbalanced). |
| **Heap / Priority Queue**| $O(1)$ min/max | $O(n)$ | $O(\log n)$ | $O(\log n)$ | Top-K elements, Dijkstra shortest path, median finding. |
| **Trie** | N/A | $O(L)$ | $O(L)$ | $O(L)$ | Prefix searching, autocomplete, spell-checker ($L$ = word length). |

---

## 3. Algorithm Toolbox

- **Sorting**: Quicksort ($O(n \log n)$ avg, in-place), Merge Sort ($O(n \log n)$ stable, extra memory), Counting/Bucket Sort ($O(n + k)$ non-comparison).
- **Binary Search**: Lower bound, Upper bound, rotated sorted arrays, binary search on answer space.
- **Graph Traversal**:
  - BFS (Breadth-First Search): Shortest path in unweighted graph, level-order traversal.
  - DFS (Depth-First Search): Cycle detection, topological sort, connected components.
  - Dijkstra: Shortest path with non-negative edge weights using Min-Heap.
- **Dynamic Programming (DP)**:
  - Memoization (Top-down) vs Tabulation (Bottom-up).
  - Common patterns: 0/1 Knapsack, Longest Common Subsequence (LCS), Longest Increasing Subsequence (LIS), Coin Change.

---

## 4. Local Repository Reference
- Tiếng Việt đầy đủ: [translations/README-vi.md](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/coding-interview-university/translations/README-vi.md)
- Bản tiếng Anh: [README.md](file:///c:/xampp/htdocs/ql_cuahang_laptop/repo/coding-interview-university/README.md)
